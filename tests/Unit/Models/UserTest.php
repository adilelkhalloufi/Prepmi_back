
<?php

use App\Models\Categorie;
use App\Models\Order;
use App\Models\Product;
use App\Models\Profil;
use App\Models\Specialitie;
use App\Models\Unite;
use App\Models\User;

it('to array', function (): void {
    $user = User::factory()->create()->refresh();
    expect(array_keys($user->toArray()))->tobe([
        User::COL_ID,
        User::COL_FIRST_NAME,
        User::COL_LAST_NAME,
        User::COL_EMAIL,
        User::COL_PHONE,
        User::COL_ADDRESS,
        User::COL_CITY_ID,
        User::COL_ROLE,
        User::COL_STATUS,
        User::COL_SPECIALITIE_ID,
        User::COL_EMAIL_VERIFIED_AT,
        User::COL_CODE_VERIFY,
        User::COL_CREATED_AT,
        User::COL_UPDATED_AT,
    ]);
});

it('may have products', function (): void {
    $categories = Categorie::factory()->count(10)->create();
    $unite = Unite::factory()->count(10)->create();
    $user = User::factory()->hasProducts(3)->create();

    expect($user->products)->toHaveCount(3)
        ->each->toBeInstanceOf(Product::class);
});

it('may have favorites', function (): void {
    $categories = Categorie::factory()->count(10)->create();
    $unite = Unite::factory()->count(10)->create();
    $products = Product::factory(5)->create();
    $user = User::factory()->create();

    expect($products)->toHaveCount(5);

    $user->favorites()->attach($products->take(3)); // Attach 3 products as favorites

    expect($user->favoriteProducts)->toHaveCount(3);

    $favoriteProductIds = $user->favoriteProducts->pluck('id')->toArray();
    expect($favoriteProductIds)->toEqual($products->take(3)->pluck('id')->toArray());
});

it('may have  interests', function (): void {
    $user = User::factory()->create();
    $categories = Categorie::factory()->count(3)->create();

    $user->interests()->attach($categories->take(2));
    expect($user->interests)->toHaveCount(2);
});

it('may have bids', function (): void {
    $categories = Categorie::factory()->count(10)->create();
    $unite = Unite::factory()->count(10)->create();
    $products = Product::factory()->count(5)->create();
    $user = User::factory()->create();

    $user->bidproducts()->attach($products->take(3)); // Attach 3 products as bids

    expect($user->bidproducts)->toHaveCount(3);
});

it('may have orders', function (): void {
    $categories = Categorie::factory()->count(10)->create();
    $unite = Unite::factory()->count(10)->create();
    $products = Product::factory(5)->create();
    $user = User::factory()->hasOrders(3)->create();

    expect($user->orders)->toHaveCount(3)
        ->each->toBeInstanceOf(Order::class);
});

it('may have a specialitie', function (): void {
    $user = User::factory()->create();
    $specialitie = Specialitie::factory()->create();

    $user->specialitie()->associate($specialitie)->save();

    expect($user->specialitie)->toBeInstanceOf(Specialitie::class);
});

it('may have a profil', function (): void {
    // test realtion user has on profil
    $profil = Profil::factory()->create();

    expect($profil->user)->toBeInstanceOf(User::class);
});
