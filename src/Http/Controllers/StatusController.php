<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Support\Permissions;
use crudPackage\Support\Status;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StatusController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(
        [
            'model' => ['required', 'string', Rule::in(array_keys(Status::models()))],
            'id'    => ['required', 'integer'],
        ]);

        Permissions::authorize(Status::permission($data['model']));

        $model  = Status::modelFor($data['model']);
        $record = $model::findOrFail($data['id']);
        $column = Status::column();
        $value  = Status::isActive($record) ? 0 : 1;

        $record->forceFill([$column => $value])->save();

        return back()->with('status', $value === 1 ? 'Kayıt aktif edildi.' : 'Kayıt pasife alındı.');
    }
}
