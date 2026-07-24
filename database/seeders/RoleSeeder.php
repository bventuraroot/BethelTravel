<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role1 = Role::firstOrCreate(['name' => 'Admin'], ['guard_name' => 'web']);
        $role2 = Role::firstOrCreate(['name' => 'Contabilidad'], ['guard_name' => 'web']);
        $role3 = Role::firstOrCreate(['name' => 'Ventas'], ['guard_name' => 'web']);
        $role4 = Role::firstOrCreate(['name' => 'Caja'], ['guard_name' => 'web']);

        $permissions = [
            // Dashboard
            'dashboard.index', 'dashboard.store', 'dashboard.update',

            // Clientes
            'client.index', 'client.create', 'client.store', 'client.update', 'client.destroy',

            // Empresas
            'company.index', 'company.store', 'company.update', 'company.destroy',

            // Usuarios (user.* en web.php)
            'user.index', 'user.store', 'user.update', 'user.destroy', 'users.index', 'users.store', 'users.update', 'users.destroy',

            // Roles y Permisos
            'rol.index', 'rol.store', 'rol.update', 'rol.destroy',
            'permission.index', 'permission.store', 'permission.update', 'permission.destroy',

            // Proveedores y Productos
            'provider.index', 'provider.store', 'provider.update', 'provider.destroy',
            'product.index', 'product.store', 'product.update', 'product.destroy',

            // Ventas, Compras y Créditos
            'sale.index', 'sale.create', 'sale.store', 'sale.update', 'sale.destroy',
            'purchase.index', 'purchase.store', 'purchase.update', 'purchase.destroy',
            'credit.index', 'credit.store', 'credit.update', 'credit.destroy',

            // Cotizaciones y Prechequeo
            'quote.index', 'quote.create', 'quote.store', 'quote.update', 'quote.destroy',
            'precheckin.index', 'precheckin.store', 'precheckin.update', 'precheckin.destroy',

            // Catálogos: Aerolíneas, Hoteles, Aeropuertos, Correlativos
            'airline.index', 'airline.store', 'airline.update', 'airline.destroy',
            'hotel.index', 'hotel.store', 'hotel.update', 'hotel.destroy',
            'airport.index', 'airport.store', 'airport.update', 'airport.destroy',
            'correlativos.index', 'correlativos.store', 'correlativos.update', 'correlativos.destroy',

            // Manuales y Backups
            'manuals.index', 'manuals.create', 'manuals.store', 'manuals.update', 'manuals.destroy',
            'backups.index', 'backups.create', 'backups.download', 'backups.delete', 'backups.clean', 'backups.refresh',

            // Reportes
            'report.index', 'report.sales', 'report.purchases', 'report.contribuyentes', 'report.consumidor',
            'report.fex', 'report.fse', 'report.ncr', 'report.rec', 'report.bookpurchases', 'report.ivacontrol',
            'report.hacienda-anexos',

            // Administración DTE y Notas
            'dte.dashboard', 'dte.errores', 'dte.contingencias',
            'credit-notes.index', 'credit-notes.store', 'credit-notes.update', 'credit-notes.destroy',
            'debit-notes.index', 'debit-notes.store', 'debit-notes.update', 'debit-notes.destroy',
            'email-purchases.index', 'email-purchases.store', 'email-purchases.update', 'email-purchases.destroy',

            // Configuración
            'config.index', 'config.store', 'config.update', 'config.destroy',

            // API
            'api.index', 'api.store', 'api.update'
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm], ['guard_name' => 'web']);
        }

        $role1->givePermissionTo(Permission::all());
        $role2->givePermissionTo(['client.index', 'company.index', 'sale.index', 'purchase.index', 'report.index']);
    }
}
