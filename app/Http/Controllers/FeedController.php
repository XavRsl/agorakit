<?php

namespace App\Http\Controllers;

use Config;
use Suin\RSSWriter\Channel;
use Suin\RSSWriter\Feed;

class FeedController extends Controller
{
    public function discussions()
    {
        $feed = new Feed();

        $channel = new Channel();
        $channel
        ->title(setting('name').' : '.trans('messages.latest_discussions'))
        ->description(setting('name'))
        ->ttl(60)
        ->appendTo($feed);

        $discussions = \App\Discussion::with('group')
        ->with('user')
        ->orderBy('created_at', 'desc')
        ->whereIn('group_id', \App\Group::publicgroups()->get()->pluck('id'))
        ->take(20)->get();

        foreach ($discussions as $discussion) {
            $item = new \Suin\RSSWriter\Item();
            $item
            ->title($discussion->name)
            ->description($discussion->body)
            ->contentEncoded($discussion->body)
            ->url(route('groups.discussions.show', [$discussion->group, $discussion]))
            ->author($discussion->user->name)
            ->pubDate($discussion->created_at->timestamp)
            ->guid(route('groups.discussions.show', [$discussion->group, $discussion]), true)
            ->preferCdata(true) // By this, title and description become CDATA wrapped HTML.
            ->appendTo($channel);
        }

        return $feed;
    }

    public function actions()
    {
        $feed = new Feed();

        $channel = new Channel();
        $channel
        ->title(setting('name').' : '.trans('messages.agenda'))
        ->description(setting('name'))
        ->ttl(60)
        ->appendTo($feed);

        $actions = \App\Action::with('group')
        ->with('user')
        ->whereIn('group_id', \App\Group::publicgroups()->get()->pluck('id'))
        ->orderBy('start', 'desc')->take(20)->get();

        foreach ($actions as $action) {
            $item = new \Suin\RSSWriter\Item();
            $item
            ->title($action->name)
            ->description($action->body)
            ->contentEncoded($action->body)
            ->url(route('groups.actions.show', [$action->group, $action]))
            ->author($action->user->name)
            ->pubDate($action->start->timestamp)
            ->guid(route('groups.actions.show', [$action->group, $action]), true)
            ->preferCdata(true) // By this, title and description become CDATA wrapped HTML.
            ->appendTo($channel);
        }

        return $feed;
    }
}
