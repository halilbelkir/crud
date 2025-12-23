<div class="tab-pane fade show active" id="columns" role="tabpanel">
    @if($value->description == 2)
        <div class="row">
            <div class="col-md-6">
                <h2 class="mb-4 pb-3 border-gray-500 border-bottom">Eski Veriler</h2>
                @foreach($values->old as $oldKey => $oldAttribute)
                    @if($oldKey != 'created_at' && $oldKey != 'updated_at' && $oldKey != 'id')
                        <div class="card @if(isset($diff[$oldKey])) border-danger @endif mb-5 mb-xl-10">
                            <div class="card-header @if(isset($diff[$oldKey])) bg-danger @endif collapsible cursor-pointer rotate" data-bs-toggle="collapse" data-bs-target="#{{$oldKey}}">
                                <div class="card-title">{{$oldKey}}</div>
                                <div class="card-toolbar rotate-180">
                                    <i class="ki-duotone ki-down fs-1"></i>
                                </div>
                            </div>
                            <div id="{{$oldKey}}" class="collapse show">
                                <div class="card-body fs-4">
                                    {{ $oldAttribute }}
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="col-md-6">
                <h2 class="mb-4 pb-3 border-gray-500 border-bottom">Yeni Veriler</h2>
                @foreach($values->new as $newKey => $newAttribute)
                    @if($newKey != 'created_at' && $newKey != 'updated_at' && $newKey != 'id')
                        <div class="card @if(isset($diff[$newKey])) border-success @endif mb-5 mb-xl-10">
                            <div class="card-header @if(isset($diff[$newKey])) bg-success @endif collapsible cursor-pointer rotate" data-bs-toggle="collapse" data-bs-target="#{{$newKey}}">
                                <div class="card-title">{{$newKey}}</div>
                                <div class="card-toolbar rotate-180">
                                    <i class="ki-duotone ki-down fs-1"></i>
                                </div>
                            </div>
                            <div id="{{$newKey}}" class="collapse show">
                                <div class="card-body fs-4">
                                    {{ $newAttribute }}
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @else
        @foreach($values->attributes as $key => $attribute)
            @if($key != 'created_at' && $key != 'updated_at' && $key != 'id')
                <div class="card mb-5 mb-xl-10">
                    <div class="card-header collapsible cursor-pointer rotate" data-bs-toggle="collapse" data-bs-target="#{{$key}}">
                        <div class="card-title">{{$key}}</div>
                        <div class="card-toolbar rotate-180">
                            <i class="ki-duotone ki-down fs-1"></i>
                        </div>
                    </div>
                    <div id="{{$key}}" class="collapse show">
                        <div class="card-body fs-4">
                            {{ $attribute }}
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endif

</div>