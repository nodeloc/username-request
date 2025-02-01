<?php

/*
 * This file is part of fof/username-request.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\UserRequest\Command;

use Carbon\Carbon;
use Flarum\User\UserValidator;
use FoF\UserRequest\UsernameRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Contracts\Events\Dispatcher;
use Flarum\Settings\SettingsRepositoryInterface;
use Mattoid\MoneyHistory\Event\MoneyHistoryEvent;
use Flarum\Foundation\ValidationException;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateRequestHandler
{
    /**
     * @var UserValidator
     */
    protected $validator;

    protected $settings;
    protected $events;
    private $translator;

    /**
     * CreateRequestHandler constructor.
     *
     * @param UserValidator $validator
     */
    public function __construct(SettingsRepositoryInterface $settings,Dispatcher $events,UserValidator $validator)
    {
        $this->validator = $validator;
        $this->settings = $settings;
        $this->events = $events;
        $this->translator = resolve(TranslatorInterface::class);

    }

    /**
     * @param CreateRequest $command
     *
     * @throws \Flarum\User\Exception\PermissionDeniedException
     * @throws \Illuminate\Validation\ValidationException'
     *
     * @return mixed
     */
    public function handle(CreateRequest $command)
    {
        $actor = $command->actor;

        $actor->assertCan('user.requestUsername');

        $username = Arr::get($command->data, 'attributes.username');
        $forNickname = Arr::get($command->data, 'attributes.forNickname', false);

        $attr = $forNickname ? 'nickname' : 'username';

        $change_cost = $forNickname ? (int)$this->settings->get('fof-username-request.nickname_cost', 1) : (int)$this->settings->get('fof-username-request.username_cost', 1);
        $change_source = $forNickname ? 'CHANGE_NICKNAME' : 'CHANGE_USERNAME';
        $change_sourceDesc = $forNickname ? $this->translator->trans('fof-username-request.forum.nickname_modals.request.title') : $this->translator->trans('fof-username-request.forum.username_modals.request.title');
        if($actor->id && $change_cost>0){
            if($actor->money < $change_cost){
                throw new ValidationException([
                    'message' => $this->translator->trans('fof-username-request.forum.pending_requests.overtip', [
                        'money' => $actor->money,
                        'cost' => $change_cost
                    ])
                ]);
            }

            $actor->money = $actor->money - $change_cost;
            $this->events->dispatch(new MoneyHistoryEvent($actor, -$change_cost, $change_source, $change_sourceDesc));
            $actor->save();
        }

        // Setting nickname to username by making nickname null so
        // it falls back to username.
        if ($forNickname && $username === $actor->username) {
            $username = null;
        }

        // Allow for simply changing the case of a username, ie `user1` to `User1`
        // The UserValidator will respond by saying `this username has already been taken`, so we bypass if the username is the same
        if (Str::lower($actor->username) !== Str::lower($username)) {
            $this->validator->assertValid([$attr => $username]);
        }

        UsernameRequest::unguard();

        $usernameRequest = UsernameRequest::firstOrNew([
            'user_id'      => $actor->id,
            'for_nickname' => $forNickname,
        ]);

        $usernameRequest->user_id = $actor->id;
        $usernameRequest->requested_username = $username;
        $usernameRequest->for_nickname = $forNickname;
        $usernameRequest->status = 'Sent';
        $usernameRequest->reason = null;
        $usernameRequest->created_at = Carbon::now();

        $usernameRequest->save();

        return $usernameRequest;
    }
}
