<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\AgeCategory;
use App\Models\Club;
use App\Models\Person;
use App\Models\Reservation;
use App\Models\User;
use App\Services\PlayerCardService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClubPersonsCardFieldsTest extends TestCase
{
    use DatabaseTransactions;

    private function clubWithMembers(): Club
    {
        $club = Club::whereNotNull('user_id')->whereHas('members')->first();

        $this->assertNotNull($club, 'Aucun club avec membres disponible pour le test.');

        return $club;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'firstname'        => 'تجريبي',
            'lastname'         => 'بطاقة',
            'birth_date'       => '2015-05-10',
            'gender'           => 'ذكر',
            'education'        => 'ثانوي',
            'license_number'   => 'TST-' . random_int(100000, 999999),
            'blood_type'       => 'O+',
            'age_category_id'  => AgeCategory::orderBy('id')->value('id'),
        ], $overrides);
    }

    /* ---------- Carte ---------- */

    public function test_card_uses_the_club_activity()
    {
        $club = $this->clubWithMembers();
        $activity = Activity::where('is_active', 1)->first() ?? Activity::first();

        $club->update(['activity_id' => $activity->id]);

        $card = app(PlayerCardService::class)->build($club->members()->first());

        $this->assertSame($activity->title, $card['activity']);
    }

    public function test_card_falls_back_when_the_club_has_no_activity()
    {
        $club = $this->clubWithMembers();
        $club->update(['activity_id' => null]);

        $card = app(PlayerCardService::class)->build($club->members()->first());

        $this->assertArrayHasKey('activity', $card);
    }

    public function test_card_exposes_blood_type_and_age_category()
    {
        $club = $this->clubWithMembers();
        $member = $club->members()->first();
        $ageCategory = AgeCategory::orderBy('id')->first();

        $member->update([
            'blood_type'       => 'AB-',
            'age_category_id'  => $ageCategory->id,
            'address'          => 'شارع الاختبار',
        ]);

        $card = app(PlayerCardService::class)->build($member->fresh());

        $this->assertSame('AB-', $card['bloodType']);
        $this->assertSame($ageCategory->name, $card['category']);
        $this->assertSame('شارع الاختبار', $card['address']);
    }

    public function test_card_no_longer_renders_days_and_hours()
    {
        $club = $this->clubWithMembers();

        $html = view('person.card', [
            'card' => app(PlayerCardService::class)->build($club->members()->first()),
        ])->render();

        $this->assertStringNotContainsString('الايام والوقت', $html);
        $this->assertStringContainsString('فصيلة الدم', $html);
        $this->assertStringContainsString('النشاط', $html);
        $this->assertStringContainsString('الفئة', $html);
        $this->assertStringContainsString('العنوان', $html);
    }

    public function test_card_dimensions_are_95x55_mm()
    {
        $club = $this->clubWithMembers();

        $html = view('person.card', [
            'card' => app(PlayerCardService::class)->build($club->members()->first()),
        ])->render();

        $this->assertStringContainsString('width: 95mm', $html);
        $this->assertStringContainsString('height: 55mm', $html);
        $this->assertStringNotContainsString('85.6', $html);
        $this->assertStringNotContainsString('54.4', $html);
    }

    /* ---------- Formulaires membres ---------- */

    public function test_create_form_contains_blood_type_and_age_category()
    {
        $club = $this->clubWithMembers();

        $response = $this->actingAs(User::find($club->user_id))
                         ->get(route('club.persons.create'));

        $response->assertOk();
        $response->assertSee('name="blood_type"', false);
        $response->assertSee('name="age_category_id"', false);
        $response->assertSee('فصيلة الدم');
        $response->assertSee('الفئة العمرية');
    }

    public function test_edit_form_contains_blood_type_and_age_category()
    {
        $club = $this->clubWithMembers();
        $member = $club->members()->first();

        $response = $this->actingAs(User::find($club->user_id))
                         ->get(route('club.persons.edit', $member->id));

        $response->assertOk();
        $response->assertSee('name="blood_type"', false);
        $response->assertSee('name="age_category_id"', false);
    }

    /* ---------- Enregistrement ---------- */

    public function test_store_saves_blood_type_and_age_category()
    {
        $club = $this->clubWithMembers();
        $ageCategory = AgeCategory::orderBy('id')->first();
        $data = $this->payload(['age_category_id' => $ageCategory->id]);

        $this->actingAs(User::find($club->user_id))
             ->post(route('club.persons.store'), $data)
             ->assertRedirect(route('club.persons.index'));

        $person = Person::where('license_number', $data['license_number'])->first();

        $this->assertNotNull($person);
        $this->assertSame('O+', $person->blood_type);
        $this->assertSame((int) $ageCategory->id, (int) $person->age_category_id);
        $this->assertSame((int) $club->id, (int) $person->club_id);
    }

    public function test_update_saves_blood_type_and_age_category()
    {
        $club = $this->clubWithMembers();
        $user = User::find($club->user_id);
        $ageCategory = AgeCategory::orderBy('id')->first();
        $data = $this->payload(['age_category_id' => $ageCategory->id]);

        $this->actingAs($user)->post(route('club.persons.store'), $data);

        $person = Person::where('license_number', $data['license_number'])->first();
        $other = AgeCategory::where('id', '!=', $ageCategory->id)->first() ?? $ageCategory;

        $this->actingAs($user)
             ->post(route('club.persons.update', $person->id), array_merge($data, [
                 'blood_type'      => 'B-',
                 'age_category_id' => $other->id,
             ]))
             ->assertRedirect(route('club.persons.index'));

        $person->refresh();

        $this->assertSame('B-', $person->blood_type);
        $this->assertSame((int) $other->id, (int) $person->age_category_id);
    }

    /* ---------- Validation ---------- */

    public function test_store_rejects_an_invalid_blood_type()
    {
        $club = $this->clubWithMembers();

        $this->actingAs(User::find($club->user_id))
             ->post(route('club.persons.store'), $this->payload(['blood_type' => 'ZZ']))
             ->assertSessionHasErrors('blood_type');

        $this->assertNull(Person::where('license_number', 'ZZ')->first());
    }

    public function test_store_rejects_an_unknown_age_category()
    {
        $club = $this->clubWithMembers();

        $this->actingAs(User::find($club->user_id))
             ->post(route('club.persons.store'), $this->payload(['age_category_id' => 999999]))
             ->assertSessionHasErrors('age_category_id');
    }

    public function test_blood_type_and_age_category_are_optional()
    {
        $club = $this->clubWithMembers();
        $data = $this->payload(['blood_type' => '', 'age_category_id' => '']);

        $this->actingAs(User::find($club->user_id))
             ->post(route('club.persons.store'), $data)
             ->assertRedirect(route('club.persons.index'))
             ->assertSessionHasNoErrors();

        $person = Person::where('license_number', $data['license_number'])->first();

        $this->assertNotNull($person);
        $this->assertNull($person->blood_type);
        $this->assertNull($person->age_category_id);
    }

    /* ---------- Activite du club ---------- */

    public function test_club_activity_relation_resolves()
    {
        $club = $this->clubWithMembers();
        $activity = Activity::first();

        $club->update(['activity_id' => $activity->id]);
        $club->refresh();

        $this->assertInstanceOf(Activity::class, $club->activity);
        $this->assertSame($activity->title, $club->activity->title);
    }

    public function test_club_dossier_saves_the_activity()
    {
        $club = $this->clubWithMembers();
        $activity = Activity::where('is_active', 1)->first() ?? Activity::first();

        $this->actingAs(User::find($club->user_id))
             ->put(route('club.dossier.update'), ['activity_id' => $activity->id])
             ->assertRedirect(route('club.dossier.index'))
             ->assertSessionHasNoErrors();

        $this->assertSame((int) $activity->id, (int) $club->fresh()->activity_id);
    }

    public function test_club_dossier_rejects_an_unknown_activity()
    {
        $club = $this->clubWithMembers();

        $this->actingAs(User::find($club->user_id))
             ->put(route('club.dossier.update'), ['activity_id' => 999999])
             ->assertSessionHasErrors('activity_id');
    }

    public function test_club_dossier_form_contains_the_activity_select()
    {
        $club = $this->clubWithMembers();
        $activity = Activity::first();
        $club->update(['activity_id' => $activity->id]);

        $response = $this->actingAs(User::find($club->user_id))
                         ->get(route('club.dossier.edit'));

        $response->assertOk();
        $response->assertSee('name="activity_id"', false);
        $response->assertSee('النشاط الرياضي للنادي');
    }

    /* ---------- Une seule categorie : study_level retire du club ---------- */

    public function test_club_forms_no_longer_offer_the_duplicate_category_field()
    {
        $club = $this->clubWithMembers();
        $user = User::find($club->user_id);

        $create = $this->actingAs($user)->get(route('club.persons.create'));
        $create->assertOk();
        $create->assertDontSee('name="study_level"', false);

        $edit = $this->actingAs($user)->get(route('club.persons.edit', $club->members()->first()->id));
        $edit->assertOk();
        $edit->assertDontSee('name="study_level"', false);
    }

    public function test_club_forms_expose_exactly_one_category_select()
    {
        $club = $this->clubWithMembers();
        $user = User::find($club->user_id);

        $create = $this->actingAs($user)->get(route('club.persons.create'))->getContent();
        $edit   = $this->actingAs($user)->get(route('club.persons.edit', $club->members()->first()->id))->getContent();

        $this->assertSame(1, substr_count($create, 'name="age_category_id"'));
        $this->assertSame(1, substr_count($edit, 'name="age_category_id"'));
        $this->assertStringNotContainsString('التصنيف', $create);
    }

    public function test_updating_a_member_does_not_wipe_an_existing_study_level()
    {
        $club = $this->clubWithMembers();
        $user = User::find($club->user_id);
        $ageCategory = AgeCategory::orderBy('id')->first();
        $data = $this->payload();

        $this->actingAs($user)->post(route('club.persons.store'), $data);

        $person = Person::where('license_number', $data['license_number'])->first();
        $person->update(['study_level' => 'فئة الاكابر']);

        $this->actingAs($user)
             ->post(route('club.persons.update', $person->id), array_merge($data, [
                 'age_category_id' => $ageCategory->id,
             ]))
             ->assertRedirect(route('club.persons.index'));

        $this->assertSame('فئة الاكابر', $person->fresh()->study_level,
            'Le retrait du champ ne doit pas ecraser la valeur historique.');
    }

    public function test_person_index_shows_the_age_category()
    {
        $club = $this->clubWithMembers();
        $ageCategory = AgeCategory::orderBy('id')->first();

        $member = $club->members()->first();
        $member->update(['age_category_id' => $ageCategory->id, 'study_level' => null]);

        $response = $this->actingAs(User::find($club->user_id))
                         ->get(route('club.persons.index'));

        $response->assertOk();
        $response->assertSee($ageCategory->name);
    }

    public function test_card_shows_a_single_category()
    {
        $club = $this->clubWithMembers();
        $member = $club->members()->first();
        $ageCategory = AgeCategory::orderBy('id')->first();

        $member->update(['age_category_id' => $ageCategory->id, 'study_level' => 'فئة الاكابر']);

        $html = view('person.partials.card-piece', [
            'card' => app(PlayerCardService::class)->build($member->fresh()),
        ])->render();

        $this->assertSame(1, substr_count($html, '>الفئة<'));
        $this->assertStringNotContainsString('التصنيف', $html);
    }

    /* ---------- Adresse ---------- */

    public function test_club_forms_contain_the_address_field()
    {
        $club = $this->clubWithMembers();
        $user = User::find($club->user_id);

        $this->actingAs($user)->get(route('club.persons.create'))
             ->assertOk()
             ->assertSee('name="address"', false)
             ->assertSee('العنوان');

        $this->actingAs($user)->get(route('club.persons.edit', $club->members()->first()->id))
             ->assertOk()
             ->assertSee('name="address"', false);
    }

    public function test_store_saves_the_address()
    {
        $club = $this->clubWithMembers();
        $data = $this->payload(['address' => 'شارع الاستقلال، رقم 12']);

        $this->actingAs(User::find($club->user_id))
             ->post(route('club.persons.store'), $data)
             ->assertRedirect(route('club.persons.index'))
             ->assertSessionHasNoErrors();

        $person = Person::where('license_number', $data['license_number'])->first();

        $this->assertNotNull($person);
        $this->assertSame('شارع الاستقلال، رقم 12', $person->address);
    }

    public function test_update_saves_the_address()
    {
        $club = $this->clubWithMembers();
        $user = User::find($club->user_id);
        $data = $this->payload();

        $this->actingAs($user)->post(route('club.persons.store'), $data);

        $person = Person::where('license_number', $data['license_number'])->first();

        $this->actingAs($user)
             ->post(route('club.persons.update', $person->id), array_merge($data, [
                 'address' => 'حي الزهور، عمارة 4',
             ]))
             ->assertRedirect(route('club.persons.index'))
             ->assertSessionHasNoErrors();

        $this->assertSame('حي الزهور، عمارة 4', $person->fresh()->address);
    }

    public function test_address_is_optional()
    {
        $club = $this->clubWithMembers();
        $data = $this->payload(['address' => '']);

        $this->actingAs(User::find($club->user_id))
             ->post(route('club.persons.store'), $data)
             ->assertRedirect(route('club.persons.index'))
             ->assertSessionHasNoErrors();

        $this->assertNull(Person::where('license_number', $data['license_number'])->first()->address);
    }

    public function test_store_rejects_an_overlong_address()
    {
        $club = $this->clubWithMembers();

        $this->actingAs(User::find($club->user_id))
             ->post(route('club.persons.store'), $this->payload(['address' => str_repeat('أ', 256)]))
             ->assertSessionHasErrors('address');
    }

    public function test_card_renders_the_address_value()
    {
        $club = $this->clubWithMembers();
        $member = $club->members()->first();
        $member->update(['address' => 'شارع الإصلاح']);

        $html = view('person.partials.card-piece', [
            'card' => app(PlayerCardService::class)->build($member->fresh()),
        ])->render();

        $this->assertStringContainsString('شارع الإصلاح', $html);
        $this->assertStringContainsString('العنوان', $html);
    }

    /* ---------- Index admin/cards ---------- */

    private function admin(): User
    {
        $admin = User::whereIn('type', ['admin', 'verificateur', 'financier'])->first();

        $this->assertNotNull($admin, 'Aucun utilisateur administrateur disponible.');

        return $admin;
    }

    public function test_admin_cards_index_shows_blood_activity_and_address()
    {
        $admin = $this->admin();

        // 1) On part des personnes REELLEMENT listees par /admin/cards (la page filtre
        //    sur l'eligibilite dossier, on ne peut pas supposer qu'un membre passe).
        $html = $this->actingAs($admin)->get(route('admin.cards'))->getContent();

        preg_match_all('#class="row-check" value="(\d+)"#', $html, $m);
        $ids = array_map('intval', $m[1]);
        $this->assertNotEmpty($ids, 'Aucune personne listee sur /admin/cards.');

        // 2) On privilegie une personne rattachee a un club, pour tester l'activite de club.
        $target = null;
        foreach ($ids as $id) {
            $p = Person::find($id);
            if ($p && $p->club_id) { $target = $p; break; }
        }
        $target ??= Person::find($ids[0]);

        $activity = Activity::first();
        if ($target->club_id) {
            Club::find($target->club_id)->update(['activity_id' => $activity->id]);
        }

        $marker = 'ADRESSE-DISTINCTIVE-1093';
        $target->update([
            'blood_type' => 'AB+',
            'address'    => $marker,
        ]);

        // 3) On re-rend la page et on verifie la presence des 3 donnees.
        $html = $this->actingAs($admin)->get(route('admin.cards'))->getContent();

        $this->assertTrue(str_contains($html, '<th>فصيلة الدم</th>'), 'Colonne groupe sanguin absente.');
        $this->assertTrue(str_contains($html, '<th>النشاط</th>'), 'Colonne activite absente.');
        $this->assertTrue(str_contains($html, '<th>العنوان</th>'), 'Colonne adresse absente.');

        $this->assertTrue(str_contains($html, $marker),
            "L'adresse n'apparait pas dans /admin/cards pour la personne #{$target->id}.");
        $this->assertTrue(str_contains($html, 'AB+'), 'Le groupe sanguin n’apparait pas.');
    }

    public function test_admin_cards_index_uses_the_club_activity_for_a_company()
    {
        $companyUser = User::where('type', 'company')->first();
        $this->assertNotNull($companyUser, 'Aucun compte entreprise disponible.');

        $companyClub = Club::where('user_id', $companyUser->id)->first();
        $this->assertNotNull($companyClub, "L'entreprise n'est pas rattachee a un club.");

        $activity = Activity::first();
        $companyClub->update(['activity_id' => $activity->id]);

        $member = Person::where('user_id', $companyUser->id)->first();
        $this->assertNotNull($member, "L'entreprise n'a aucun membre.");

        // Le membre herite de l'activite de son club.
        $card = app(PlayerCardService::class)->build($member->fresh());
        $this->assertSame($activity->title, $card['activity']);

        // Et la colonne activite de /admin/cards l'affiche aussi.
        $html = $this->actingAs($this->admin())->get(route('admin.cards'))->getContent();
        $this->assertTrue(str_contains($html, e($activity->title)),
            "L'activite du club de l'entreprise n'apparait pas dans /admin/cards.");
    }

    public function test_admin_cards_index_columns_match_their_cells()
    {
        $html = $this->actingAs($this->admin())->get(route('admin.cards'))->getContent();

        $row = null;
        if (preg_match('#<tr>\s*<td><input type="checkbox" class="row-check"#', $html, $m, PREG_OFFSET_CAPTURE)) {
            $row = substr($html, $m[0][1]);
            $row = substr($row, 0, strpos($row, '</tr>'));
        }

        $this->assertNotNull($row, 'Aucune ligne de personne trouvee sur /admin/cards.');
        $this->assertSame(12, substr_count($row, '<td>'), 'Le nombre de cellules doit correspondre aux colonnes.');
        $this->assertSame(12, substr_count($html, '<th>'), 'Le nombre d’en-tetes est inattendu.');
    }

    /* ---------- Activite depuis la derniere reservation (reservations.user_id) ---------- */

    /**
     * Cree une reservation pour le compte du personne, rattachee a une
     * complex_activity dont on choisit l'activite.
     */
    private function bookReservation(Person $person, string $activityTitle): Reservation
    {
        $activity = Activity::where('title', $activityTitle)->first()
                     ?? tap(Activity::first())->update(['title' => $activityTitle]);

        $complexActivity = DB::table('complex_activity')->where('activity_id', $activity->id)->first();

        $this->assertNotNull($complexActivity,
            'Aucune complex_activity ne pointe vers cette activite : la chaine est incomplete.');

        return Reservation::create([
            'user_id'             => $person->user_id,
            'complex_activity_id' => $complexActivity->id,
            'season_id'           => DB::table('seasons')->max('id'),
            'payment_status'      => 'paid',
            'statut'              => 'confirmee',
            'qty_places'          => 1,
            'total_price'         => 0,
        ]);
    }

    public function test_last_reservation_relation_reads_reservations_by_user_id()
    {
        $person = Person::whereNotNull('user_id')->where('user_id', '!=', 0)
                        ->whereDoesntHave('lastReservation')->first();

        $this->assertNotNull($person, 'Aucune personne sans reservation trouvee.');

        $res = $this->bookReservation($person, 'activite-teste-A');

        $found = Person::with('lastReservation')->find($person->id)->lastReservation;

        $this->assertNotNull($found, 'lastReservation() doit se baser sur reservations.user_id.');
        $this->assertSame((int) $res->id, (int) $found->id);
    }

    public function test_last_reservation_picks_the_most_recent_one()
    {
        $person = Person::whereNotNull('user_id')->where('user_id', '!=', 0)
                        ->whereDoesntHave('lastReservation')->first();

        $this->assertNotNull($person, 'Aucune personne sans reservation trouvee.');

        $first  = $this->bookReservation($person, 'activite-teste-ANCIENNE');
        $second = $this->bookReservation($person, 'activite-teste-RECENTE');

        $this->assertGreaterThan($first->id, $second->id);

        $card = app(PlayerCardService::class)->build($person->fresh());

        $this->assertSame('activite-teste-RECENTE', $card['activity'],
            'La carte doit retenir la reservation la plus recente.');
    }

    public function test_card_activity_falls_back_to_the_last_reservation()
    {
        $person = Person::whereNotNull('user_id')->where('user_id', '!=', 0)
                        ->whereDoesntHave('lastReservation')->first();

        $this->assertNotNull($person, 'Aucune personne sans reservation trouvee.');

        $person->update(['club_id' => null, 'favorite_activity' => null]);

        $this->bookReservation($person, 'activite-teste-RESERVATION');

        $card = app(PlayerCardService::class)->build($person->fresh());

        $this->assertSame('activite-teste-RESERVATION', $card['activity']);
    }

    public function test_card_prefers_the_club_activity_over_the_last_reservation()
    {
        $person = Person::whereNotNull('user_id')->where('user_id', '!=', 0)
                        ->whereDoesntHave('lastReservation')->first();

        $this->assertNotNull($person, 'Aucune personne sans reservation trouvee.');

        $this->bookReservation($person, 'activite-teste-DE-RESERVATION');

        $club = Club::where('user_id', $person->user_id)->first()
              ?? Club::create(['user_id' => $person->user_id, 'nom' => 'club-test']);

        $club->update(['activity_id' => Activity::first()->id]);

        $card = app(PlayerCardService::class)->build($person->fresh());

        $this->assertSame(Activity::first()->title, $card['activity'],
            'L\'activite du club doit primer sur celle de la reservation.');
    }

    public function test_card_falls_back_to_favorite_activity_as_last_resort()
    {
        $person = Person::whereNotNull('user_id')->where('user_id', '!=', 0)
                        ->whereDoesntHave('lastReservation')->first();

        $this->assertNotNull($person, 'Aucune personne sans reservation trouvee.');

        $person->update(['club_id' => null, 'favorite_activity' => 'activite-teste-PREFEREE']);

        $card = app(PlayerCardService::class)->build($person->fresh());

        $this->assertSame('activite-teste-PREFEREE', $card['activity']);
    }

    public function test_admin_cards_index_shows_the_reservation_activity()
    {
        $person = Person::whereNotNull('user_id')->where('user_id', '!=', 0)
                        ->whereDoesntHave('lastReservation')->first();

        $this->assertNotNull($person, 'Aucune personne sans reservation trouvee.');

        // Il doit figurer sur /admin/cards : dossier valide.
        $this->actingAs($this->admin())->get(route('admin.cards'))
             ->assertOk();

        $this->bookReservation($person, 'activite-teste-VISIBLE-ADMIN');

        $html = $this->actingAs($this->admin())->get(route('admin.cards'))->getContent();

        $this->assertTrue(str_contains($html, 'activite-teste-VISIBLE-ADMIN'),
            "L'activite de la derniere reservation n'apparait pas dans /admin/cards.");
    }
}
