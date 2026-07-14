<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Dte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HaciendaAnexoTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $company;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and a company
        $this->user = User::factory()->create();
        $this->company = Company::create([
            'name' => 'Test Company',
            'email' => 'company@example.com',
            'nit' => '0614-010101-101-1',
            'ncr' => '123456-7',
            'cuenta_no' => '12345678',
            'giro' => 'Servicios',
            'tipoContribuyente' => 'Grande',
            'tipoEstablecimiento' => 'Casa Matriz',
            'logo' => 'logo.png',
        ]);

        // Create and assign Admin role and manage_users permission
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web'
        ]);
        $this->user->assignRole($adminRole);

        $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
            'name' => 'manage_users',
            'guard_name' => 'web'
        ]);
        $this->user->givePermissionTo($permission);
    }

    /**
     * Test that guests are redirected to login.
     */
    public function test_guest_cannot_access_annexes_page()
    {
        $response = $this->get(route('report.hacienda-anexos'));
        $response->assertRedirect('/login');
    }

    /**
     * Test that authenticated users can load the index page.
     */
    public function test_authenticated_user_can_access_annexes_page()
    {
        $response = $this->withoutMiddleware(\App\Http\Middleware\CheckPermission::class)
            ->actingAs($this->user)
            ->get(route('report.hacienda-anexos'));

        $response->assertOk();
        $response->assertViewIs('hacienda-anexos.index');
        $response->assertViewHas('companies');
    }

    private function seedMockSalesData()
    {
        // 1. Create a client
        $client = \App\Models\Client::create([
            'email' => 'client@example.com',
            'tpersona' => 'Natural',
            'birthday' => '1990-01-01',
            'firstname' => 'John',
            'firstlastname' => 'Doe',
            'company_id' => $this->company->id,
            'economicactivity_id' => null,
        ]);

        // 2. Create the CCF Typedocument using raw DB to force ID 3
        \DB::table('typedocuments')->insert([
            'id' => 3,
            'type' => 'CCF',
            'description' => 'Comprobante de Crédito Fiscal',
            'codemh' => '03',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Create a Sale
        $sale = Sale::create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
            'typedocument_id' => 3,
            'date' => now(),
            'state' => 1,
            'totalamount' => 10.00,
        ]);

        // 4. Create a DTE record linked to the Sale
        Dte::create([
            'sale_id' => $sale->id,
            'codigoGeneracion' => '12345-abcde',
            'id_doc' => 'DTE-12345',
            'company_id' => $this->company->id,
            'company_name' => $this->company->name,
            'versionJson' => 1,
            'tipoDte' => '03',
            'tipoModelo' => '1',
            'tipoTransmision' => '1',
            'nameTable' => 'sales',
            'codTransaction' => '1',
            'desTransaction' => 'Venta',
            'type_document' => '03',
        ]);
    }

    /**
     * Test export to CSV for Anexo 1.
     */
    public function test_export_csv_anexo1()
    {
        $this->seedMockSalesData();

        $response = $this->withoutMiddleware(\App\Http\Middleware\CheckPermission::class)
            ->actingAs($this->user)
            ->post(route('report.hacienda-anexos.export'), [
                'company_id' => $this->company->id,
                'year' => (int) date('Y'),
                'month' => (int) date('m'),
                'annex_type' => 'anexo1',
            ]);

        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'attachment; filename=' . rawurlencode('hacienda_ANEXO1_' . $this->company->id . '_' . date('Ym') . '.csv'));
    }

    /**
     * Test export to Excel for Anexo 1.
     */
    public function test_export_excel_anexo1()
    {
        $this->seedMockSalesData();

        $response = $this->withoutMiddleware(\App\Http\Middleware\CheckPermission::class)
            ->actingAs($this->user)
            ->post(route('report.hacienda-anexos.export-excel'), [
                'company_id' => $this->company->id,
                'year' => (int) date('Y'),
                'month' => (int) date('m'),
                'annex_type' => 'anexo1',
            ]);

        $response->assertOk();
        $this->assertTrue(
            str_contains($response->headers->get('Content-Type'), 'spreadsheet') || 
            str_contains($response->headers->get('Content-Type'), 'openxmlformats-officedocument.spreadsheetml.sheet')
        );
    }
}
