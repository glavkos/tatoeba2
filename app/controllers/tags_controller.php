<?php
/**
 * Tatoeba Project, free collaborative creation of multilingual corpuses project
 * Copyright (C) 2010 Allan SIMON <allan.simon@supinfo.com>
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
 * @author   Allan SIMON <allan.simon@supinfo.com>
 * @license  Affero General Public License
 * @link     http://tatoeba.org
 */

/**
 * Controller for tags
 *
 * @category Tags
 * @package  Controllers
 * @author   Allan SIMON <allan.simon@supinfo.com>
 * @license  Affero General Public License
 * @link     http://tatoeba.org
 */

class TagsController extends AppController
{
    /**
     * Controller name
     *
     * @var string
     * @access public
     */
    public $name = 'Tags';
    public $persistentModel = true;
    public $components = array('CommonSentence');
    /**
     * Before filter.
     *
     * @return void
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        // setting actions that are available to everyone, even guests
        $this->Auth->allowedActions = array(
            "show_sentences_with_tag",
            'view_all',
            'for_moderators'
        );

    }

    /**
     * Add a tag to a sentence
     * 
     * @return void
     */

    public function add_tag_post()
    {
        $tagName = $this->data['Tag']['tag_name'];
        $sentenceId = Sanitize::paranoid($this->data['Tag']['sentence_id']);
        $this->add_tag($tagName, $sentenceId);

    }

    /**
     * Add a tag to a Sentence
     *
     * @param string tagName    Name of the tag to add
     * @param int    sentenceId Id of the sentence on which the tag will added
     *
     * @return void
     */

    public function add_tag($tagName, $sentenceId)
    {
        $userId = CurrentUser::get("id");

        // If no sentence id, we redirect to homepage.
        if (empty($sentenceId) || !is_numeric($sentenceId) ) {
            $this->redirect(
                array(
                    'controller' => 'pages',
                    'action' => 'home'
                )
            );
        }

        // If empty tag, we redirect to sentence's page.
        if (empty($tagName)) {
            $this->redirect(
                array(
                    'controller' => 'sentences',
                    'action' => 'show',
                    $sentenceId
                )
            );
        }

        // save and check if the tag has been added
        if (!$this->Tag->addTag($tagName, $userId, $sentenceId)) {
            $infoMessage = sprintf(
                __(
                    "Tag '%s' already exists for sentence #%s, or cannot be added",
                    true
                ),
                $tagName,
                $sentenceId
            );
            $this->Session->setFlash($infoMessage);
        }

        $this->redirect(
            array(
                'controller' => 'sentences',
                'action' => 'show',
                $sentenceId
            )
        );

    }

    /**
     * Display all tags page
     *
     * @TODO it's only a "better than nothing" page yet
     *
     * @return void
     */
    public function view_all()
    {

        $this->helpers[] = 'Tags';

        $allTags = $this->Tag->getAllTagsOrdered();
        $this->set("allTags", $allTags);
    }

    /**
     * Remove a tag from a sentence when on the sentence page
     *
     * @param int $tagId      Id of the tag to remove from the sentence
     * @param int $sentenceId Id of the sentence to remove the tag from
     *
     * @return void
     */

    public function remove_tag_from_sentence($tagId, $sentenceId)
    {
        if (!empty($tagId) && !empty($sentenceId)) {
            $this->Tag->removeTagFromSentence($tagId, $sentenceId);
        }
        $this->redirect(
            array(
                'controller' => 'sentences',
                'action' => 'show',
                $sentenceId
            )
        );

    }


    /**
     * Remove a tag from a sentence when on the "show all sentences with
     * this tag" page
     *
     * @param int $tagId      Id of the tag to remove from this sentence
     * @param int $sentenceId Id of the sentence to remove the tag from
     *
     * @return void
     */
    public function remove_tag_of_sentence_from_tags_show($tagId, $sentenceId)
    {
        if (!empty($tagId) && !empty($sentenceId)) {
            $this->Tag->removeTagFromSentence($tagId, $sentenceId);
        }
        $this->redirect($_SERVER['HTTP_REFERER']);
    }


    /**
     * Display a list of all sentences with a given tag
     *
     * @param string $tagId           Id of the tag
     * @param string $lang            Filter only sentences in this language.
     *
     * @return void
     */
    public function show_sentences_with_tag($tagId, $lang = null)
    {
        // In case the $tagId is not an int we assume that the user
        // comes from an old URL with the internal name, so we
        // redirect them to the right URL.
        if ($tagId != '0' && intval($tagId) == 0) {
            $actualTagId = $this->Tag->getIdFromInternalName($tagId);
            $this->redirect(
                array(
                    "controller" => "tags",
                    "action" => "show_sentences_with_tag",
                    $actualTagId, $lang
                ),
                301
            );
        }
            
        $this->helpers[] = 'Pagination';
        $this->helpers[] = 'CommonModules';
        $this->helpers[] = 'Tags';
        
        $tagName = $this->Tag->getNameFromId($tagId);
        $tagExists = !empty($tagName);
        $this->set('tagExists', $tagExists);
        $this->set('tagId', $tagId);

        if ($tagExists) {
            $this->paginate = $this->Tag->paramsForPaginate($tagId, 10, $lang);

            $sentencesIdsTaggerIds = $this->paginate('TagsSentences');

            $taggerIds = array();
            $sentenceIds = array();

            foreach ($sentencesIdsTaggerIds as $sentenceIdTaggerId) {
                $taggerIds[] = $sentenceIdTaggerId['TagsSentences']['user_id'];
                $sentenceIds[] = $sentenceIdTaggerId['TagsSentences']['sentence_id'];
            }
            $allSentences = $this->CommonSentence->getAllNeededForSentences(
                $sentenceIds
            );

            $this->set('langFilter', $lang);
            $this->set('allSentences', $allSentences);
            $this->set('tagName', $tagName);
            $this->set('taggerIds', $taggerIds);
        } else {
            $this->Session->setFlash(
                __(
                    'There are no sentences for this tag. The tag you are looking '.
                    'for has been deleted or does not exist.', true
                )
            );
        }
    }


    /**
     * List sentences with a certain id that were tagged logger ago than
     * the grace (warning) period within which sentence owners are supposed to respond to comments.
     * A "moderator" is known on the site as a "corpus maintainer".
     *
     * @param string $tagName         Tag name.
     * @param string $lang            Language of the sentences.
     *
     * @return void
     */
    public function for_moderators($tagId = null, $lang = null) {
        // If no tag name was specified, assume that the name "@change" (the most 
        // generic tag indicating attention from moderators) was intended.
        $tagChangeName = $this->Tag->getChangeTagName();
        $tagCheckName = $this->Tag->getCheckTagName();
        $tagDeleteName = $this->Tag->getDeleteTagName();
        $tagNeedsNativeCheckName = $this->Tag->getNeedsNativeCheckTagName();
        $tagOKName = $this->Tag->getOKTagName();
        $tagChangeId = $this->Tag->getIdFromName($tagChangeName);
        $tagCheckId = $this->Tag->getIdFromName($tagCheckName);
        $tagDeleteId = $this->Tag->getIdFromName($tagDeleteName);
        $tagNeedsNativeCheckId = $this->Tag->getIdFromName($tagNeedsNativeCheckName);
        $tagOKId = $this->Tag->getIdFromName($tagOKName);
        if (empty($tagId)) {
            $tagId = $tagChangeId;
        }

        $this->helpers[] = 'Pagination';
        $this->helpers[] = 'CommonModules';
        $this->helpers[] = 'Sentences';
        
        // Get sentences that have been tagged longer ago than the grace period.
        $results = $this->Tag->TagsSentences->getSentencesWithNonNewTag(
            $tagId, $lang
        );

        $tagName = $this->Tag->getNameFromId($tagId);    
        $this->set('tagId', $tagId);
        $this->set('tagName', $tagName);
        $this->set('results', $results);
        $this->set('tagChangeName', $tagChangeName);
        $this->set('tagCheckName', $tagCheckName);
        $this->set('tagDeleteName', $tagDeleteName);
        $this->set('tagNeedsNativeCheckName', $tagNeedsNativeCheckName);
        $this->set('tagOKName', $tagOKName);
        $this->set('tagChangeId', $tagChangeId);
        $this->set('tagCheckId', $tagCheckId);
        $this->set('tagDeleteId', $tagDeleteId);
        $this->set('tagNeedsNativeCheckId', $tagNeedsNativeCheckId);
        $this->set('tagOKId', $tagOKId);
    }
}
?>
