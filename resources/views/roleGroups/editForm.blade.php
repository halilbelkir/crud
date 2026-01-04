<form id="addUpdateForm" class="form row justify-content-center fv-plugins-bootstrap5 fv-plugins-framework"
      method="post" action="{{route('role-groups.update',$value->id)}}">
    @method('PUT')
    <div class="form-group col-12 mb-7 fv-plugins-icon-container">
        <label class="required fw-semibold fs-6 mb-2">Başlık</label>
        <input type="text" name="title"
               value="{{$value->title}}"
               class="form-control form-control-solid mb-3 mb-lg-0"
               placeholder="Başlık">
    </div>

    <div class="row form-group p-0 m-0">
        <label class="fw-bold fs-4">İzinler</label>
        <div class="validation" style="margin-bottom: -10px;"></div>
        <div class="col-12 separator border-2 my-5"></div>
        @foreach($cruds as $crud)
            <div class="col-12 col-lg-6 col-xl-4 pb-4">
                <label class="fw-bold fs-6 mb-5">{{$crud->title}}</label>
                <div class="form-check form-check-sm mb-3">
                    <input class="form-check-input" onchange="allCheck(this)" name="permissions_all" id="role_{{$crud->id}}_all" data-checkbox-class="role_{{$crud->id}}" type="checkbox" value="{{$crud->id}}"/>
                    <label class="form-check-label text-gray-700 fw-semibold" for="role_{{$crud->id}}_all">
                        Hepsini Seç
                    </label>
                </div>
                @foreach($permissions as $permissionKey => $permission)
                    <div class="form-check form-check-sm mb-3">
                        <input class="form-check-input role_{{$crud->id}}"
                               @if(!empty($crud->roles($value->id)) && $crud->roles($value->id)->{$permission['column']} == 1) checked @endif
                               id="role_{{$permissionKey}}" name="permissions[{{$crud->id}}][{{$permissionKey}}]" type="checkbox" value="1"/>
                        <label class="form-check-label text-gray-700 fw-semibold" for="role_{{$permissionKey}}">
                            {{$permission['title']}}
                        </label>
                    </div>
                @endforeach
            </div>
        @endforeach
        @if(count($specialMenus) > 0)
            @php $permission = $permissions[1]; $permissionKey = 1; @endphp
            @foreach($specialMenus as $specialMenu)
                <div class="col-12 col-lg-6 col-xl-4 pb-4">
                    <label class="fw-bold fs-6 mb-5">{{$specialMenu->title}}</label>
                    <div class="form-check form-check-sm mb-3">
                        <input class="form-check-input special_role_{{$specialMenu->id}}"
                               @if(!empty($specialMenu->roles($value->id)) && $specialMenu->roles($value->id)->{$permission['column']} == 1) checked @endif
                               id="special_role_{{$permissionKey}}" name="special_permissions[{{$specialMenu->id}}][{{$permissionKey}}]" type="checkbox" value="1"/>
                        <label class="form-check-label text-gray-700 fw-semibold" for="special_role_{{$permissionKey}}">
                            {{$permission['title']}}
                        </label>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div class="pt-5 col-lg-4 col-xl-2 text-center">
        <button type="submit" class="btn btn-primary buttonForm w-100"> Kaydet</button>
        @include('crudPackage::components.loading')
    </div>
</form>