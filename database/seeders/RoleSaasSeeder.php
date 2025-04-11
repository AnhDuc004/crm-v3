<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSaasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // create permissions
        Permission::create(['name' => 'create task']);
        Permission::create(['name' => 'edit task']);
        Permission::create(['name' => 'delete task']);
        Permission::create(['name' => 'view task']);
        Permission::create(['name' => 'view owner task']);

        // create permissions
        Permission::create(['name' => 'create customer']);
        Permission::create(['name' => 'edit customer']);
        Permission::create(['name' => 'delete customer']);
        Permission::create(['name' => 'view customer']);
        Permission::create(['name' => 'view owner customer']);

        // create permissions
        Permission::create(['name' => 'create project']);
        Permission::create(['name' => 'edit project']);
        Permission::create(['name' => 'delete project']);
        Permission::create(['name' => 'view project']);
        Permission::create(['name' => 'view owner project']);

        // create permissions
        Permission::create(['name' => 'create staff']);
        Permission::create(['name' => 'edit staff']);
        Permission::create(['name' => 'delete staff']);
        Permission::create(['name' => 'view staff']);
        Permission::create(['name' => 'view owner staff']);

        // update cache to know about the newly created permissions (required if using WithoutModelEvents in seeders)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();


        // create roles and assign created permissions

        // this can be done as separate statements
        $role = Role::create(['name' => 'Staff']);
        $role->givePermissionTo('edit task');
        $role->givePermissionTo('view task');

        $role = Role::create(['name' => 'SaleStaff']);
        $role->givePermissionTo('edit customer');
        $role->givePermissionTo('view customer');
        $role->givePermissionTo('edit task');
        $role->givePermissionTo('view task');
        
        // or may be done by chaining
        $role = Role::create(['name' => 'Manager'])
            ->givePermissionTo(['edit task', 'delete task', 'view task', 'view owner task']);
        
        $role = Role::create(['name' => 'SaleManager'])
            ->givePermissionTo(['edit task', 'delete task', 'view task', 'view owner task'])
            ->givePermissionTo(['edit customer', 'delete customer', 'view customer', 'view owner customer']);
                    
        // $role = Role::create(['name' => 'Manager'])
        //     ->givePermissionTo(['edit customer', 'delete customer', 'view customer', 'view owner customer']);
        

        $role = Role::create(['name' => 'Director']);
        $role->givePermissionTo(Permission::all());

        $role = Role::create(['name' => 'Administrator']);
        $role->givePermissionTo(['edit task', 'delete task', 'view task', 'view owner task'])
             ->givePermissionTo(['edit staff', 'delete staff', 'view staff', 'view owner staff']);
    }
}
