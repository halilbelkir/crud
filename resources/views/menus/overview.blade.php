<div class="tab-pane fade show active" id="billingInformation" role="tabpanel">
    <ul id="menu" class="nested-sortable">
        @foreach($value->parentItems as $parentItem)
            <li class="parent" data-id="{{$parentItem->id}}">
                {{$parentItem->title}}
                <small> - {{$parentItem->route}}</small>
                <ul class="children">
                    @if($parentItem->children)
                        @foreach($parentItem->children as $children)
                            <li data-id="{{$children->id}}">{{$children->title}} <small> - {{$children->route}}</small></li>
                        @endforeach
                    @endif
                </ul>
            </li>
        @endforeach
    </ul>
</div>