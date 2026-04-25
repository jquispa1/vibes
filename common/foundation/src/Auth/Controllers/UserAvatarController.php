<?php namespace Common\Auth\Controllers;

use App\Models\User;
use Common\Auth\Events\UserAvatarChanged;
use Common\Core\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserAvatarController extends BaseController
{
    public function __construct(
        protected Request $request,
        protected User $user,
    ) {
    }

    public function store(User $user)
    {
        $this->authorize('update', $user);

        $this->validate($this->request, [
            'file' => [
                'required_without:url',
                'image',
                'mimes:jpeg,jpg,png,gif,webp,bmp,svg',
                'mimetypes:image/jpeg,image/png,image/gif,image/webp,image/bmp,image/svg+xml',
                'max:1500',
            ],
            'url' => [
                'required_without:file',
                'string',
                'max:500',
                function (string $attribute, string $value, \Closure $fail): void {
                    $value = trim($value);
                    if ($value === '') {
                        $fail(__('The :attribute field is required.', ['attribute' => $attribute]));
                        return;
                    }
                    // Full URL: require HTTP or HTTPS
                    if (str_contains($value, '://')) {
                        if (!filter_var($value, FILTER_VALIDATE_URL)) {
                            $fail(__('The :attribute format is invalid.', ['attribute' => $attribute]));
                            return;
                        }
                        $parsed = parse_url($value);
                        $scheme = $parsed['scheme'] ?? '';
                        if (!in_array(strtolower($scheme), ['http', 'https'], true)) {
                            $fail(__('Avatar URL must use HTTP or HTTPS.'));
                        }
                        return;
                    }
                    // Storage path: allow avatars/ or storage/avatars/ style paths, no path traversal
                    if (str_contains($value, '..') || preg_match('#[<>"|?*]#', $value)) {
                        $fail(__('The :attribute format is invalid.', ['attribute' => $attribute]));
                    }
                },
            ],
        ]);

        // delete old user avatar
        if ($user->getRawOriginal('image')) {
            Storage::disk('public')->delete($user->getRawOriginal('image'));
        }

        // store new avatar on public disk (file upload) or use validated URL as path
        $path = $this->request->filled('url')
            ? $this->request->get('url')
            : $this->request
                ->file('file')
                ->storePublicly('avatars', ['disk' => 'public']);

        // attach avatar to user model
        $user->image = $path;
        $user->save();

        event(new UserAvatarChanged($user));

        return $this->success(['user' => $user]);
    }

    public function destroy(User $user)
    {
        $this->authorize('update', $user);

        if ($user->getRawOriginal('image')) {
            Storage::disk('public')->delete($user->getRawOriginal('image'));
        }

        $user->image = null;
        $user->save();

        event(new UserAvatarChanged($user));

        return $this->success();
    }
}

