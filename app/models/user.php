<?php
/**
 * Tatoeba Project, free collaborative creation of multilingual corpuses project
 * Copyright (C) 2009  BEN YAALA Salem <salem.benyaala@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Tatoeba
 * @author   BEN YAALA Salem <salem.benyaala@gmail.com>
 * @license  Affero General Public License
 * @link     http://tatoeba.org
 */

/**
 * Model for users.
 *
 * @category Users
 * @package  Models
 * @author   BEN YAALA Salem <salem.benyaala@gmail.com>
 * @author   HO Ngoc Phuong Trang <tranglich@gmail.com>
 * @license  Affero General Public License
 * @link     http://tatoeba.org
 */
class User extends AppModel
{

    /**
     *
     * @var string
     */
    public $name = 'User';

    /**
     *
     * @var array
     */
    public $actsAs = array(
        'Acl' => array('type' => 'requester'),
        'Containable'
    );

    public $recursive = -1;

    // contributor vs. advanced contributor vs. corpus maintainer vs. admin
    const LOWEST_TRUST_GROUP_ID = 4;

    // trustworthy vs. untrustworthy 
    const MIN_LEVEL = -1; // trustworthy
    const MAX_LEVEL = 0; // untrustworthy (submits bad or copyrighted sentences)

    /**
     *
     * @var array
     */
    public $validate = array(
        'username' => array(
            'alphanumeric' => array('rule' => '/^\\w*$/'),
            'isUnique' => array('rule' => 'isUnique'),
            'min' => array('rule' => array('minLength', 2))
        ),
        'email' => array(
            'email' => array('rule' => 'email'),
            'isUnique' => array('rule' => 'isUnique')
        ),
        'lastlogout' => array('numeric'),
        'status' => array('numeric'),
        'permissions' => array('numeric'),
        'level' => array('numeric'),
        'group_id' => array('numeric')
    );

    /**
     *
     * @var array
     */
    public $belongsTo = array(
        'Group' => array(
            'className' => 'Group',
            'foreignKey' => 'group_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );

    /**
     *
     * @var array
     */
    public $hasMany = array(
        'SentenceComments',
        'Contributions',
        'Sentences',
        'SentencesLists',
        'Wall' => array('foreignKey' => 'owner')
    );

    /**
     *
     * @var array
     */
    public $hasAndBelongsToMany = array(
        'Favorite' => array(
            'className' => 'Favorite',
            'joinTable' => 'favorites_users',
            'foreignKey' => 'user_id',
            'associationForeignKey' => 'favorite_id',
        )
    );

    /**
     * ?
     *
     * @return array
     */
    public function parentNode()
    {
        if (!$this->id && empty($this->data)) {
            return null;
        }
        if (isset($this->data['User']['group_id'])) {
            $groupId = $this->data['User']['group_id'];
        } else {
            $groupId = $this->field('group_id');
        }
        if (!$groupId) {
            return null;
        } else {
            return array('Group' => array('id' => $groupId));
        }
    }

    /**
     * Generate a random password.
     *
     * @return string
     */
    public function generatePassword()
    {
        $pw = '';
        $c  = 'bcdfghjklmnprstvwz' . 'BCDFGHJKLMNPRSTVWZ' ;
        //consonants except hard to speak ones
        $v  = 'aeiou';              //vowels
        $a  = $c.$v;                //both

        //use two syllables...
        for ($i=0; $i < 2; $i++) {
            $pw .= $c[rand(0, strlen($c)-1)];
            $pw .= $v[rand(0, strlen($v)-1)];
            $pw .= $a[rand(0, strlen($a)-1)];
        }
        //... and add a nice number
        $pw .= rand(1, 9);

        $pw = trim($pw);

        if (strlen($pw) == 7) {
            $pw .= rand(0, 9);
        }

        return $pw;
    }

    /**
     * get all the information needed to generate the user's profile
     *
     * @param integer $userId User Identifiant
     *
     * @return array
     */
    public function getInformationOfCurrentUser($userId)
    {
        return $this->findById($userId);
    }

    /**
     * get all the information needed to generate a user profile
     *
     * @param string $userName User's screen name
     *
     * @return array
     */
    public function getInformationOfUser($userName)
    {
        return $this->find(
            'first',
            array(
                'conditions' => array('username' => $userName),
                'fields' => array(
                    'id',
                    'name',
                    'image',
                    'homepage',
                    'since',
                    'send_notifications',
                    'description',
                    'username',
                    'birthday',
                    'is_public',
                    'group_id',
                    'lang',
                    'level',
                    'country_id'
                )
            )
        );
    }


    /**
     * Get options.
     *
     * @param int $userId Id of the user.
     *
     * @return array
     */
    public function getSettings($userId)
    {
        return $this->find(
            'first',
            array(
                'conditions' => array('id' => $userId),
                'fields' => array(
                    'is_public',
                    'send_notifications',
                    'email',
                    'lang'
                )
            )
        );
    }


    /**
     * get all the information about a user needed by the Wall
     *
     * @param integer $userId User Indentifiant
     *
     * @return array
     */
    public function getInfoWallUser($userId)
    {
        $result = $this->find(
            'first',
            array(
                'conditions' => array('User.id' => $userId),
                'fields' => array(
                    'User.image',
                    'User.username',
                    'User.id'
                )
            )
        );

        return $result ;
    }

    /**
     * Get user data + sentences, contributions, favorites, etc.
     *
     * @param int|null $id Id of the user. If null we take a random one.
     *
     * @return void
     */
    public function getUserByIdWithExtraInfo($id = null)
    {
        //TODO: HACK SPOTTED user of order rand
        if ($id == null) {
            // TODO add containable here
            $user = $this->find(
                'first',
                array(
                    'conditions' => 'User.group_id < 5',
                    'order' => 'RAND()',
                    'limit' => 1,
                )
            );
        } else {
            $user = $this->find(
                'first',
                array(
                    'conditions' => array('User.id' => $id),
                    'contain' => array(
                        'Favorite' => array(
                            'limit' => 10,
                            'fields' => array(
                                'id',
                                'lang',
                                'correctness',
                                'text',
                            )
                        ),
                        'Sentences' => array(
                            'limit' => 10,
                            'fields' => array(
                                'id',
                                'lang',
                                'correctness',
                                'text',
                            ),
                            'order' => 'modified DESC'
                        ),
                        'Contributions' => array(
                            'limit' => 10,
                            'fields' => array(
                                'sentence_id',
                                'sentence_lang',
                                'translation_id',
                                'action',
                                'datetime',
                                'text',
                            ),
                            'order' => 'datetime DESC '
                        ),
                        'SentenceComments' => array(
                            'limit' => 10,
                            'fields' => array(
                                'id',
                                'text',
                                'created',
                                'sentence_id',
                                'hidden',
                                'modified'
                            ),
                            'order' => 'created DESC'
                        ),
                        'Wall' => array(
                            'limit' => 10,
                            'fields' => array(
                                'id',
                                'content',
                                'date',
                                'hidden',
                                'modified'
                            ),
                            'order' => 'date DESC'
                        ),
                        'Group' => array(
                            'fields' => array('name')
                        )
                    )
                )
            );
        }
        return $user;
    }
    
    
    /**
     * Retrieves only the fields from users table, no joins.
     *
     * @param int $id Id of the user.
     *
     * @return array User data.
     */
    public function getUserById($id)
    {
        $user = $this->find(
            'first',
            array(
                'conditions' => array('User.id' => $id)
            )
        );
        
        return $user;
    }
    
    
    /**
     * Return id of a user from the username.
     *
     * @param string $username Username.
     *
     * @return int
     */
    public function getIdFromUsername($username)
    {
        $user = $this->find(
            'first',
            array(
                'conditions' => array('User.username' => $username),
                'fields' => 'User.id'
            )
        );
        return $user['User']['id'];
    }


    /**
     * Return name of a user from the user's id.
     *
     * @param int $userId User's id.
     *
     * @return string
     */
    public function getUserNameFromId($userId)
    {
        $user = $this->find(
            'first',
            array(
                'conditions' => array('User.id' => $userId),
                'fields' => 'User.username'
            )
        );
        return $user['User']['username'];
    }


    /**
     * Return array of (id => username).
     *
     * @param array $userIds Array of user ids.
     *
     * @return array
     */
    public function getUsernamesFromIds($usersIds)
    {
        $results = $this->find(
            'all',
            array(
                'conditions' => array('id' => $usersIds),
                'fields' => array('id', 'username')
            )
        );

        $users = array();
        foreach($results as $result) {
            $id = $result['User']['id'];
            $username = $result['User']['username'];
            $users[$id] = $username;
        }

        return $users;
    }

    /**
     * Return id of a user from the email.
     *
     * @param string $userEmail user email.
     *
     * @return int
     */
    public function getIdFromEmail($userEmail)
    {
        $user = $this->find(
            'first',
            array(
                'conditions' => array('User.email' => $userEmail),
                'fields' => 'User.id'
            )
        );
        return $user['User']['id'];
    }

    /**
     * Return an email from a user id.
     *
     * @param int $userId user id.
     *
     * @return string
     */
    public function getEmailFromId($userId)
    {
        $user = $this->find(
            'first',
            array(
                'conditions' => array('User.id' => $userId),
                'fields' => 'User.email'
            )
        );
        return $user['User']['email'];
    }

    /**
     * Check if (new) email for user is unique
     *
     * @param string $email new email.
     *
     * @return bool
     */
    public function isEmailUnique($email, $userId)
    {
        $result =  $this->find(
            'first',
            array(
                'conditions' => array(
                    'email' => $email,
                    'User.id !=' => $userId
                )
           )
        );
        if (empty($result)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return password of a user.
     *
     * @param int $userId Id of the user.
     *
     * @return string
     */
    public function getPassword($userId)
    {
        $user = $this->find(
            'first',
            array(
                'conditions' => array('User.id' => $userId),
                'fields' => 'User.password'
            )
        );
        return $user['User']['password'];
    }

    /**
     * Return number of members who have validated their registration.
     *
     * @return int
     */

    public function getNumberOfActiveMembers()
    {
        return $this->find(
            'count',
            array(
                'conditions' => array(
                    'group_id <' => 5
                )
            )
        );
    }  
    
    
    /**
     * Return the level of the user of given id.
     *
     * @param int $userId Id of the user.
     *
     * @return int
     */
    public function getLevelOfUser($userId)
    {
        $result = $this->find(
            'first',
            array(
                'conditions' => array('User.id' => $userId),
                'fields' => 'User.level'
            )
        );
        return $result['User']['level'];
    }


    public function getGroupOfUser($userId)
    {
        $result = $this->findById($userId, 'group_id');

        return $result['User']['group_id'];
    }

}


?>
