<?php
/*
    Tatoeba Project, free collaborativ creation of languages corpuses project
    Copyright (C) 2009  TATOEBA Project(should be changed)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
if(!isset($ajax)){
	$navigation->displayUsersNavigation($user['User']['id'], $user['User']['username']);

	echo '<h3>';
	__('User followers');
	echo '</h3>';
}

if(count($user['Follower']) > 0){
	echo '<ul>';
	foreach($user['Follower'] as $follower){
		// quick fix in order to have custom resolution on image and have image in link
		echo '<li class="followerAvatar">
				<a href="/user/profile/'.$follower['username'].'" title="'.$follower['username'].'">
					<img src="/img/profiles/' .
					(empty($follower['image'])?'unknown-avatar.jpg' : $follower['image']) .
					'" alt="'.$follower['username'].'" ' .
					(isset($ajax)?'style="width:50px;"/>':'/><br/>'.$follower['username']).'
				</a>';
			if($user['User']['id'] == $session->read('Auth.User.id')){
				echo '<a href="/followers/refuse_follower/'.$follower['id'].'" class="blockFollower" title="' . sprintf(__('Block %s', true), $follower['username']) . '">
					<span class="blockFollowerContent">' . __('Block this person', true) . '</span>
				</a>';
			}
		echo '</li>';
	}
	echo '</ul>';
	if(isset($ajax)){
		echo '<p style="clear:both;">' .
			$html->link(__('Display more followers', true), array('action' => 'followers', $user['User']['id'])) .
			'</p>';
	}
}else{
	__('This user does not have any followers.');
}
?>