<div class="tab-pane fade" id="columnsTab" role="tabpanel">
    <ul id="columns" class="nested-sortable">
        @foreach($value->items as $item)
            <li class="parent" data-id="{{$item->id}}">{{$item->title}} </li>
        @endforeach
    </ul>
</div>