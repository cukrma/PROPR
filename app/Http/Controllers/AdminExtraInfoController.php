<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Child;
use App\Models\User;
use App\Models\ExtraInfo;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;
use App\Traits\EmailConfig;

class AdminExtraInfoController extends Controller
{
    use EmailConfig;

    public function index()
    {
        return view('admin.extra', [
            'children' => Child::all(),
            'show' => Category::first(),
        ]);
    }

    public function download()
    {
        $html = view('admin.extra', [
            'children' => Child::all(),
            'show' => Category::first(),
        ])->render();

        Browsershot::html($html)->margins(10, 5, 10, 5)->hideHeader()->format('A4')->save("dodatecneInformace.pdf");
        return redirect('/dodatecneInformace.pdf');
    }

    public function destroy()
    {
        ExtraInfo::truncate();
        return back()->with('success', 'Zprávy byly odstraněny!');
    }

    public function show()
    {
        $show = Category::first();
        if ($show->slug == "false") {
            $a['slug'] = "true";
            $show->update($a);

            $data = [
                'type' => "extraInfo",
                'subject' => "Dodatečné informace",
            ];
            $response = $this->createEmail($data);
    
            $idsOfUsersWithChild = Child::all()->pluck('user_id')->toArray(); // pole id uzivatelu pro kazde dite
            $idsOfUsersWithChild = array_unique($idsOfUsersWithChild); // odstrani duplikaty
            array_push($idsOfUsersWithChild, 1); // pridat admina

            // pole emailu vsech uzivatelu, kteri maji vytvorene alespon jedno dite
            $users_emails = User::all()->whereIn('id', $idsOfUsersWithChild)->pluck('email')->toArray();

            $this->sendEmailTo($response['email'], $users_emails);

            return back()->with('success', 'Dodatečné informace zobrazeny!');
        } else {
            $a['slug'] = "false";
            $show->update($a);
            return back()->with('success', 'Dodatečné informace skryty!');
        }
    }
}
