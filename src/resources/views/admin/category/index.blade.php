@extends('layouts.app')
@section('content')

<x-admin-headline />
<section class="item-list-wrapper">
<div class="flex justify-center"><x-flash-message status="session('status')"/></div>
  <div class="category-list">
    @foreach ($categories as $cat)
      <div class="category-block">
        <h2>{{ $cat->name }}</h2>
        <ul class="sub-category-list">
          @foreach ($cat->subCategories as $sub)
            <li>
              <strong>{{ $sub->name }}</strong>
              <ul class="item-category-list">
                @foreach ($sub->itemCategories as $itemCat)
                  <li>
                    @can('category.update')
                      <a href="{{ route('admin.itemCategory.edit', ['itemCategory' => $itemCat]) }}">
                        {{ $itemCat->name }}
                      </a>
                    @else
                      <span class="text-[#002f61]">{{ $itemCat->name }}</span>
                    @endcan
                  </li>
                @endforeach
              </ul>
            </li>
          @endforeach
        </ul>
      </div>
    @endforeach
  </div>
</section>

@endsection