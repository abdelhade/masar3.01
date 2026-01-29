        // Example: Notify all users (or filter by permission/role)
        // You can change User::all() to specific users
        $users = \App\Models\User::all();
        \Illuminate\Support\Facades\Notification::send($users, new \Modules\Notifications\Notifications\GeneralNotification(
            title: __('New Chance Source Created'),
            message: __('A new chance source named ":name" has been added.', ['name' => $chanceSource->name ?? 'New Source']),
            url: route('chance-sources.index'),
            type: 'success',
            icon: 'las la-plus-circle'
        ));
