<?php

namespace App\Listeners;

use App\Events\UserUpdated;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateUserPhoto
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserUpdated $event)
    {
        $user = $event->user;
        $photo = $event->photo;

        // Handle image upload if a new file is provided
        if ($photo) {
            // Delete the old photo if it exists and is not the default photo
            if ($user->photo && Storage::disk('public')->exists('profile/' . $user->photo)) {
                Storage::disk('public')->delete('profile/' . $user->photo);
            }

            // Generate filename with user ID prefix and original filename
            $newFilename = $photo->hashName($user->id);

            // Store the new photo in 'profile' folder under 'public' disk with the new filename
            $photo->storeAs('profile', $newFilename, 'public');

            // Update the user's photo field with just the filename
            $user->photo = $newFilename;
            $user->save();
        }
    }
}
