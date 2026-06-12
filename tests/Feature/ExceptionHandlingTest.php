<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExceptionHandlingTest extends TestCase
{
    public function test_404_redirects_back()
    {
        // Simulasi request dari '/admin/dashboard'
        $response = $this->withHeader('referer', url('/admin/dashboard'))
                         ->get('/rute-yang-tidak-ada-12345');
                         
        $response->assertStatus(302);
        $response->assertRedirect('/admin/dashboard');
        $response->assertSessionHas('error', 'Halaman tidak ditemukan.');
    }

    public function test_404_without_referer_redirects_to_login()
    {
        $response = $this->get('/rute-yang-tidak-ada-12345');
                         
        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
    }
}
