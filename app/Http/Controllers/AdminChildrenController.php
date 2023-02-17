<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;
use App\Traits\EmailConfig;


class AdminChildrenController extends Controller
{
    use EmailConfig;

    public function index()
    {
        return view('admin.children.index', [
            'childrenForApproval' => Child::latest()->where('status', 'like', 'Čeká na schválení')->paginate(200),
            'children' => Child::latest()->where('status', '=', 'Závazně přihlášen na tábor')->orWhere('status', '=', 'Zaplaceno')->paginate(200)
        ]);
    }

    public function confirm(Child $child)
    {
        $attributes['status'] = "Závazně přihlášen na tábor";
        $child->update($attributes);

        $data = [
            'type' => "applicationConfirm",
            'subject' => "Přihláška potvrzena",
            'child' => $child
        ];
        $this->sendEmailToParentOfChild($data);

        return redirect('/admin/children')->with('success', 'Přihlášení potvrzeno!');
    }

    public function reject(Child $child)
    {
        $attributes['status'] = "Přihláška zamítnuta";
        $child->update($attributes);

        $data = [
            'type' => "applicationReject",
            'subject' => "Přihláška zamítnuta",
            'child' => $child
        ];
        $this->sendEmailToParentOfChild($data);

        return redirect('/admin/children')->with('success', 'Přihlášení zamítnuto!');
    }

    public function paid()
    {

        $attributes = request()->all();
        $paid_ids = array_keys($attributes);
        array_shift($paid_ids); // odstraneni prvniho prvku (token)
        $children = Child::all()->where('status', "Závazně přihlášen na tábor");

        foreach ($children as $child) {
            if (in_array($child->id, $paid_ids) && $child->paid_status != "Zaplaceno") { // nove prijata platba
                $a['paid'] = 1;
                $a['paid_status'] = "Zaplaceno";
                $child->update($a);

                $data = [
                    'type' => "paid",
                    'subject' => "Platba přijata",
                    'child' => $child
                ];

                $this->sendEmailToParentOfChild($data);
            } elseif (!in_array($child->id, $paid_ids) && $child->paid_status == "Zaplaceno") { // zruseni prijate platby
                $a['paid'] = 0;
                $a['paid_status'] = "Čekáme na příchod vaší platby";
                $child->update($a);
            }
        }
        return redirect('/admin/children')->with('success', 'Uloženo!');
    }

    public function download()
    {
        $html = view('admin.children.download', [
            'children' => Child::latest()->where('status', '=', 'Závazně přihlášen na tábor')->orWhere('status', '=', 'Zaplaceno')->paginate(200)
        ])->render();

        Browsershot::html($html)->margins(10, 5, 10, 5)->hideHeader()->format('A4')->save("seznamDeti.pdf");
        return redirect('/seznamDeti.pdf');
    }

}
