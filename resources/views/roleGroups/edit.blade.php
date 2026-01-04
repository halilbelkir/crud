@extends('crudPackage::layout.main',['activePage' => $value->title.' Düzenle','parentPage' => 'Yetkiler','parentPageRoute' => route('role-groups.index')])
@section('content')
    <div class="card">
        <div class="card-body py-4">
            @include('crudPackage::roleGroups.editForm')
        </div>
    </div>
@endsection
@section('js')
    <script>
        setTimeout(function()
        {
            permissionCheckboxCheck();
        }, 300);
    </script>
@endsection