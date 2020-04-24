<?php

namespace LibrarianApp;

use Exception;
use Librarian\Html\Bootstrap;
use Librarian\Html\Element;
use Librarian\ItemMeta;
use Librarian\Media\Temporal;
use Librarian\Mvc\TextView;

class SummaryView extends TextView {

    /**
     * @var Temporal
     */
    private $temporal_obj;

    /**
     * Main.
     *
     * @param array $item
     * @return string
     * @throws Exception
     */
    public function main(array $item): string {

        $this->temporal_obj = $this->di->getShared('Temporal');

        $this->title($item['title']);

        $this->head();

        /** @var Bootstrap\Breadcrumb $el */
        $el = $this->di->get('Breadcrumb');

        $el->style('margin: 0 -15px');
        $el->addClass('bg-transparent');
        $el->item('IL', IL_BASE_URL . 'index.php/#dashboard/main');
        $el->item($item['title']);
        $bc = $el->render();

        $el = null;

        $IL_BASE_URL = IL_BASE_URL;

        // PDF button.
        if (!empty($item['file_hash'])) {

            /** @var Bootstrap\IconButton $el */
            $el = $this->di->get('IconButton');

            $el->elementName('a');
            $el->href("{$IL_BASE_URL}index.php/pdf/main?id={$item['id']}");
            $el->target('_blank');
            $el->addClass('px-2 py-1 border-0');
            $el->context('secondary');
            $el->icon('open-in-new');
            $new_window = $el->render();

            $el = null;

            /** @var Bootstrap\Button $el */
            $el = $this->di->get('Button');

            $el->elementName('a');
            $el->href("#pdf/main?id={$item['id']}");
            $el->addClass('px-2 py-2 border-0');
            $el->context('primary');
            $el->html('PDF');
            $pdf = $el->render();

            $el = null;

            /** @var Bootstrap\IconButton $el */
            $el = $this->di->get('IconButton');

            $el->elementName('a');
            $el->href("{$IL_BASE_URL}index.php/pdf/file?disposition=attachment&id={$item['id']}");
            $el->addClass('px-2 py-1 border-0');
            $el->context('secondary');
            $el->icon('download');
            $download = $el->render();

            $el = null;

        } else {

            /** @var Bootstrap\IconButton $el */
            $el = $this->di->get('IconButton');

            $el->addClass('px-2 py-1 bg-darker-5 border-0');
            $el->icon('open-in-new');
            $el->disabled('disabled');
            $new_window = $el->render();

            $el = null;

            /** @var Bootstrap\Button $el */
            $el = $this->di->get('Button');

            $el->addClass('px-2 py-2 bg-darker-5 border-0');
            $el->html('PDF');
            $el->disabled('disabled');
            $pdf = $el->render();

            $el = null;

            /** @var Bootstrap\IconButton $el */
            $el = $this->di->get('IconButton');

            $el->addClass('px-2 py-1 bg-darker-5 border-0');
            $el->icon('download');
            $el->disabled('disabled');
            $download = $el->render();

            $el = null;
        }

        $pdf_link = <<<EOT
            <div class="btn-group-vertical">
                $new_window
                $pdf
                $download
            </div>
EOT;

        // Authors.
        $authors = '<div class="mb-1">No authors</div>';

        if (!empty($item['authors'])) {

            $authors = join('; ', $item['authors']);
            $authors = "<div class=\"truncate mb-1\">$authors</div>";

        }

        // Editors.
        $editors = '';

        if (!empty($item['editors'])) {

            $editors = join('; ', $item['editors']);
            $editors = "<div class=\"truncate mb-1\">Edited by: $editors</div>";
        }

        // Links.
        $links = '';

        // External UIDs.
        if (!empty($item[ItemMeta::COLUMN['UID_TYPES']])) {

            foreach ($item[ItemMeta::COLUMN['UID_TYPES']] as $key => $type) {

                $value = $item[ItemMeta::COLUMN['UIDS']][$key];

                switch ($type) {

                    case 'DOI':
                        $doi_lmth = $this->sanitation->lmth($value);
                        $doi_url = $this->sanitation->urlquery($doi_lmth);
                        $doi_attr = $this->sanitation->attr($doi_url);
                        $links .= empty($doi_url) ? "" : "<a class=\"mr-3\" href=\"https://dx.doi.org/{$doi_attr}\">Publisher</a>";
                        break;

                    case 'PMID':
                        $name = 'Pubmed';
                        $value_lmth = $this->sanitation->lmth($value);
                        $value_url = $this->sanitation->urlquery($value_lmth);
                        $value_html = $this->sanitation->attr($value_url);
                        $href = 'https://www.ncbi.nlm.nih.gov/pubmed/' . $value_html;
                        $links .= "<a class=\"mr-3\" href=\"$href\">$name</a> ";
                        $href = 'https://www.ncbi.nlm.nih.gov/pubmed?linkname=pubmed_pubmed&from_uid=' . $value_html;
                        $links .= "<a class=\"mr-3\" href=\"$href\">Similar</a> ";
                        $href = 'https://www.ncbi.nlm.nih.gov/pubmed?linkname=pubmed_pubmed_citedin&from_uid=' . $value_html;
                        $links .= "<a class=\"mr-3\" href=\"$href\">Cited&nbsp;in</a> ";
                        break;

                    case 'PMCID':
                        $name = 'PMC';
                        $value_lmth = $this->sanitation->lmth($value);
                        $value_url = $this->sanitation->urlquery($value_lmth);
                        $value_html = $this->sanitation->attr($value_url);
                        $href = 'https://www.ncbi.nlm.nih.gov/pmc/' . $value_html;
                        $links .= "<a class=\"mr-3\" href=\"$href\">$name</a> ";
                        break;


                }
            }
        }

        if (!empty($item['urls'])) {

            $url_arr = explode('|', $item['urls']);

            foreach ($url_arr as $url) {

                $links .= "<a class=\"mr-3\" href=\"$url\">" . parse_url($url, PHP_URL_HOST) . "</a> ";
            }
        }

        // I, Librarian stable link.
        $IL_BASE_URL = IL_BASE_URL;
        $links .= "<a class=\"mr-3\" href=\"{$IL_BASE_URL}stable.php?id={$item['id']}\">Stable&nbsp;link</a>";

        // Project button.
        /** @var Bootstrap\Icon $el */
        $el = $this->di->get('Icon');

        $el->icon('chevron-down');
        $el->addClass('mdi-18px text-muted');
        $chevron = $el->render();

        $el = null;

        /** @var Element $el */
        $el = $this->di->get('Element');

        $el->elementName('button');
        $el->style('transform: translate(-1px, -1px)');
        $el->addClass('p-0 pr-1 mr-2 projects-button');
        $el->dataToggle('collapse');
        $el->dataTarget("#projects-{$item['id']}");
        $el->html("{$chevron}Projects");
        $project_button = $el->render();

        $el = null;

        // Clipboard.
        /** @var Bootstrap\Input $el */
        $el = $this->di->get('Input');

        $el->type('checkbox');
        $el->inline(true);
        $el->addClass('clipboard');
        $el->label('Clipboard');
        $el->name('clipboard');

        if ($item['in_clipboard'] === 'Y') {

            $el->checked('checked');
        }

        $clipboard_check = $el->render();

        $el = null;

        // Projects.
        $project_html = empty($item['projects']) ? '<span class="text-secondary">No projects yet.</span>' : '';

        if (!empty($item['projects'])) {

            foreach ($item['projects'] as $project) {

                /** @var Bootstrap\Input $el */
                $el = $this->di->get('Input');

                $el->type('checkbox');
                $el->inline(true);
                $el->label($project['project']);
                $el->name('project');
                $el->value($project['project_id']);
                $el->addClass('project');
                $el->id("project-{$item['id']}-{$project['project_id']}");

                if ($project['in_project'] === 'Y') {

                    $el->attr('checked', 'checked');
                }

                $project_html .= $el->render();

                $el = null;
            }
        }

        /** @var Bootstrap\Card $el */
        $el = $this->di->get('Card');

        $el->body(<<<TOP
            <table style="table-layout: fixed;width:100%">
                <tbody>
                    <tr>
                        <td class="pt-2" rowspan="2" style="vertical-align: top;width: 3.8em;">
                            $pdf_link
                        </td>
                        <td>
                           <span class="text-muted text-uppercase">{$item['reference_type']} #{$item['id']}</span>
                            <h5><b>{$item['title']}</b></h5>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            $authors
                            $editors
                            $links<br>
                            $project_button
                            $clipboard_check
                            <div class="collapse" id="projects-{$item['id']}">$project_html</div>
                        </td>
                    </tr>
                </tbody>
            </table>
TOP
        , null, 'px-4 py-3');
        $top_card = $el->render();

        $el = null;

        // Abstract.
        /** @var Element $el Abstract. */
        $el = $this->di->get('Element');

        $el->elementName('a');
        $el->href('#edit?id='. $item['id']);
        $el->html('Edit');
        $edit_button = $el->render();

        $el = null;

        /** @var Bootstrap\Card $el */
        $el = $this->di->get('Card');

        $el->addClass('item-card-row h-100');
        $el->header("<b>ABSTRACT</b> $edit_button");
        $el->body($item['abstract'] . '<br><br>');
        $abstract_card = $el->render();

        $el = null;

        // Graphical abstract.
        $graphical_abstract = '';

        foreach ($item['files'] as $file) {

            if ($file['name'] === 'graphical_abstract') {

                $IL_BASE_URL = IL_BASE_URL;

                $graphical_abstract = <<<GRAPHICAL
                    <img alt="Graphical abstract" class="w-100" src="{$IL_BASE_URL}index.php/supplements/download?id={$item['id']}&filename=graphical_abstract">
GRAPHICAL;

                break;
            }
        }

        /** @var Bootstrap\Card $el */
        $el = $this->di->get('Card');

        $el->addClass('item-card-row h-100');
        $el->header("<b>GRAPHICAL ABSTRACT</b> <a href=\"#supplements?id={$item['id']}\">Edit</a>");
        $el->body($graphical_abstract, null, 'p-0');
        $graphical_abstract_card = $el->render();

        $el = null;

        // Notes.
        $notes = '';

        /** @var Bootstrap\Icon $el */
        $el = $this->di->get('Icon');

        $el->addClass('text-secondary mdi-24px');
        $el->icon('account');
        $user_icon = $el->render();

        $el = null;

        if (!empty($item['notes'])) {

            foreach ($item['notes'] as $note) {

                // Display users notes at the top.
                if ($note['id_hash'] === $this->session->data('user_id')) {

                    $note_html = $this->sanitation->lmth($note['note']);
                    $notes = "<p>$user_icon<b>{$note['name']}</b></p><div id=\"user-note\">$note_html</div>$notes";
                    continue;
                }

                $note_html = $this->sanitation->lmth($note['note']);
                $notes .= "<p>$user_icon<b>{$note['name']}</b></p><div id=\"user-note\">$note_html</div>";
            }
        }

        /** @var Bootstrap\Button $el */
        $el = $this->di->get('Button');

        $el->context('link');
        $el->addClass('open-notes px-1 py-0 border-0');
        $el->dataItemId($item['id']);
        $el->html('Edit');
        $note_button = $el->render();

        $el = null;

        /** @var Bootstrap\Card $el */
        $el = $this->di->get('Card');

        $el->addClass('item-card-row h-100');
        $el->header("<b>NOTES</b> $note_button");
        $el->body($notes);
        $notes_card = $el->render();

        $el = null;

        // Supplements.
        $supplements = '';

        foreach ($item['files'] as $file) {

            // Escape for URL link.
            $file_lmth = $this->sanitation->lmth($file['name']);
            $file_url = $this->sanitation->urlquery($file_lmth);
            $file_attr = $this->sanitation->attr($file_url);

            /** @var Bootstrap\IconButton $el */
            $el = $this->di->get('IconButton');

            $el->elementName('a');
            $el->addClass('btn-secondary btn-round mr-2');
            $el->href(IL_BASE_URL . "index.php/supplements/download?id={$item['id']}&filename={$file_attr}&disposition=attachment");
            $el->name('download');
            $el->icon('download');
            $download_button = $el->render();

            $el = null;

            $supplements .=  <<<EOT
            <p>
                $download_button
                <a href="{$IL_BASE_URL}index.php/supplements/download?id={$item['id']}&filename={$file_attr}"
                    target="_blank"
                    class="filename-link">
                    {$file['name']}
                </a>
            </p>
EOT;
        }

        /** @var Element $el */
        $el = $this->di->get('Element');

        $el->elementName('a');
        $el->href('#supplements?id='. $item['id']);
        $el->html('Edit');
        $supplements_button = $el->render();

        $el = null;

        /** @var Bootstrap\Card $el */
        $el = $this->di->get('Card');

        $el->addClass('item-card-row h-100');
        $el->header("<b>SUPPLEMENTS</b> $supplements_button");
        $el->body($supplements);
        $supplements_card = $el->render();

        $el = null;

        // PDF notes.
        $pdfnotes = '';

        if (!empty($item['pdfnotes'])) {

            foreach ($item['pdfnotes'] as $note) {

                $note_html = $this->sanitation->lmth($note['annotation']);
                $pdfnotes .= "<p><b>{$note['name']}:</b> $note_html</p>";
            }
        }

        /** @var Bootstrap\Card $el */
        $el = $this->di->get('Card');

        $el->addClass('item-card-row h-100');
        $el->header('<b>PDF NOTES</b>');
        $el->body($pdfnotes);
        $pdfnotes_card = $el->render();

        $el = null;

        // Tags.
        $tags = '';

        if (!empty($item['tags'])) {

            $tags = '<p>'. join('</p><p>', $item['tags']) . '</p>';
        }

        /** @var Element $el */
        $el = $this->di->get('Element');

        $el->elementName('a');
        $el->href('#tags/item?id='. $item['id']);
        $el->html('Edit');
        $tag_button = $el->render();

        $el = null;

        /** @var Bootstrap\Card $el */
        $el = $this->di->get('Card');

        $el->addClass('item-card-row h-100');
        $el->header("<b>TAGS</b> $tag_button");
        $el->body($tags);
        $tags_card = $el->render();

        $el = null;

        // UIDs.
        /** @var Bootstrap\Descriptionlist $el */
        $el = $this->di->get('Descriptionlist');

        $el->term('I, Librarian', 'col-xl-3');
        $el->description($item['id'], 'col-xl-9 pb-2');

        $el->term('Citation key', 'col-xl-3');
        $el->description($item[ItemMeta::COLUMN['BIBTEX_ID']], 'col-xl-9 pb-2');

        // External UIDs.
        if (!empty($item[ItemMeta::COLUMN['UID_TYPES']])) {

            foreach ($item[ItemMeta::COLUMN['UID_TYPES']] as $key => $type) {

                $value = $item[ItemMeta::COLUMN['UIDS']][$key];
                $form = '';

                if (in_array($type, ['ARXIV', 'DOI', 'IEEE', 'PMID', 'PMCID', 'NASAADS'])) {

                    /** @var Bootstrap\Input $inp */
                    $inp = $this->di->get('Input');

                    $inp->type('hidden');
                    $inp->name('id');
                    $inp->value($item['id']);
                    $hidden_item_id = $inp->render();

                    $inp = null;

                    /** @var Bootstrap\Input $inp */
                    $inp = $this->di->get('Input');

                    $inp->type('hidden');
                    $inp->name('type');
                    $inp->value($type);
                    $hidden_type = $inp->render();

                    $inp = null;

                    /** @var Bootstrap\Input $inp */
                    $inp = $this->di->get('Input');

                    $inp->type('hidden');
                    $inp->name('uid');
                    $inp->value($value);
                    $hidden_uid = $inp->render();

                    $inp = null;

                    /** @var Bootstrap\Button $el */
                    $btn = $this->di->get('Button');

                    $btn->type('submit');
                    $btn->context('outline-danger');
                    $btn->componentSize('small');
                    $btn->html('Autoupdate');
                    $update = $btn->render();

                    $btn = null;

                    /** @var Bootstrap\Form $f */
                    $f = $this->di->get('Form');

                    $f->addClass('form-uid');
                    $f->action($IL_BASE_URL . 'index.php/item/autoupdate');
                    $f->html($hidden_item_id . $hidden_type . $hidden_uid . $update);
                    $form = $f->render();

                    $f = null;
                }

                $el->term(ItemMeta::UID_TYPE[$type] ?? 'Other', 'col-xl-3');
                $el->description($value . $form, 'col-xl-9 d-flex justify-content-between align-items-center');
            }
        }

        $uids = $el->render();

        $el = null;

        /** @var Bootstrap\Card $el */
        $el = $this->di->get('Card');

        $el->addClass('item-card-row h-100');
        $el->header("<b>IDENTIFIERS</b> $edit_button");
        $el->body($uids);
        $uid_card = $el->render();

        $el = null;

        // Miscellaneous list.
        /** @var Bootstrap\Descriptionlist $el */
        $el = $this->di->get('Descriptionlist');

        // Keywords.
        if (!empty($item['keywords'])) {

            $keywords = join('; ', $item['keywords']);
            $el->term('Keywords', 'col-xl-3 text-truncate');
            $el->description($keywords, 'col-xl-9');
        }

        // Publication.
        if (!empty($item['primary_title'] . $item['secondary_title']. $item['tertiary_title'])) {

            $primary = empty($item['primary_title']) ? '' : "{$item['primary_title']}<br>";
            $secondary = empty($item['secondary_title']) ? '' : "{$item['secondary_title']}<br>";
            $tertiary = empty($item['tertiary_title']) ? '' : "{$item['tertiary_title']}";

            $el->term('Publication', 'col-xl-3 text-truncate');
            $el->description($primary . $secondary. $tertiary, 'col-xl-9');
        }

        if (!empty($item['publication_date'])) {

            $el->term('Date', 'col-xl-3 text-truncate');
            $el->description($this->temporal_obj->toLocalDate($item['publication_date']), 'col-xl-9');
        }

        if (!empty($item['volume'] . $item['issue']. $item['pages'])) {

            $v = empty($item['volume']) ? '' : "volume {$item['volume']}";
            $i = empty($item['issue']) ? '' : "issue {$item['issue']}";
            $p = empty($item['pages']) ? '' : "pages {$item['pages']}";

            $el->term('Pages', 'col-xl-3 text-truncate');
            $el->description("$v $i $p", 'col-xl-9');
        }

        // Affiliation.
        if (!empty($item['affiliation'])) {

            $el->term('Affiliation', 'col-xl-3 text-truncate');
            $el->description("<div class=\"truncate\">{$item['affiliation']}</div>", 'col-xl-9');
        }

        // Publisher.
        if (!empty($item['publisher'])) {

            $place = empty($item['place_published']) ? '' : " ({$item['place_published']})";

            $el->term('Publisher', 'col-xl-3 text-truncate');
            $el->description($item['publisher'] . $place, 'col-xl-9');
        }

        // Custom columns 1-8.
        for ($i = 1; $i <= 8; $i++) {

            if (empty($item['custom' . $i])) {

                continue;
            }

            $custom_name = $this->app_settings->getGlobal('custom' . $i);

            $el->term($custom_name, 'col-xl-3 text-truncate');
            $el->description($item['custom' . $i], 'col-xl-9');
        }

        $dl = $el->render();

        $el = null;

        // Misc card.
        /** @var Bootstrap\Card $el */
        $el = $this->di->get('Card');

        $el->addClass('item-card-row h-100');
        $el->header("<b>MISCELLANEOUS</b>  <a href=\"#edit?id={$item['id']}\">Edit</a>");
        $el->body($dl);
        $misc_card = $el->render();

        $el = null;

        // Added by.
        $this->temporal_obj = $this->di->getShared('Temporal');
        $added_time = $this->temporal_obj->toUserTime($item['added_time']);

        /** @var Bootstrap\Descriptionlist $el */
        $el = $this->di->get('Descriptionlist');
        $el->addClass('mb-3');
        $el->term('Added', 'col-sm-1 ml-4');
        $el->description("{$added_time} by {$item['name']}", 'col-sm-10 ml-4');

        $dl = $el->render();

        $el = null;

        // Grid.
        /** @var Bootstrap\Row $el */
        $el = $this->di->get('Row');

        $el->addClass('row-cols-1 row-cols-xl-2 no-gutters');
        $el->column($abstract_card, 'col mb-3 pr-xl-2');
        $el->column($graphical_abstract_card, 'col mb-3 pl-xl-2');
        $el->column($notes_card, 'col mb-3 pr-xl-2');
        $el->column($pdfnotes_card, 'col mb-3 pl-xl-2');
        $el->column($supplements_card, 'col mb-3 pr-xl-2');
        $el->column($uid_card, 'col mb-3 pl-xl-2');
        $el->column($tags_card, 'col mb-3 pr-xl-2');
        $el->column($misc_card, 'col mb-3 pl-xl-2');
        $grid = $el->render();

        $el = null;

        /** @var Bootstrap\Row $el */
        $el = $this->di->get('Row');

        $el->id('top-row');
        $el->addClass('d-flex align-content-start');
        $el->style('overflow: auto;height: 100vh');
        $el->column($bc, 'col-12');
        $el->column($top_card, 'col-12 mb-3');
        $el->column($grid);
        $el->column($dl,'col-md-12 mb-3');
        $content = $el->render();

        $el = null;

        // Toolbar row.

        $server = $this->request->getServerParams();

        $btn_class = self::$theme === 'dark' ? 'secondary' : 'outline-dark';

        /** @var Bootstrap\Button $el */
        $el = $this->di->get('Button');

        $el->id('open-export');
        $el->context($btn_class);
        $el->addClass('border-0');
        $el->dataToggle('modal');
        $el->dataTarget('#modal-export');
        $el->attr('data-export-url', IL_BASE_URL . 'index.php/' . IL_PATH_URL . '?' . $server['QUERY_STRING']);
        $el->html('Export');
        $export_button = $el->render();

        $el = null;

        /** @var Bootstrap\Icon $el */
        $el = $this->di->get('Icon');

        $el->addClass('mdi-24px text-danger');
        $el->icon('alert');
        $alert = $el->render();

        $el = null;

        $alert_escaped = $this->sanitation->attr($alert);

        /** @var Bootstrap\Button $el */
        $el = $this->di->get('Button');

        $el->id('delete-item');
        $el->context('danger');
        $el->addClass('border-0');
        $el->html('Delete');
        $el->dataTitle('Delete item?');
        $el->dataBody("{$alert_escaped} The item will be deleted permanently.");
        $delete_button = $el->render();

        $el = null;

        // Email.
        $subject = rawurlencode($item['title']);
        $body = rawurlencode(<<<BODY
Check out this item in the library:

{$item['title']}

{$IL_BASE_URL}index.php/item#summary?id={$item['id']}
BODY
);

        /** @var Bootstrap\Button $el */
        $el = $this->di->get('Button');

        $el->elementName('a');
        $el->context($btn_class);
        $el->addClass('border-0');
        $el->href('mailto:?subject=' . $subject . '&body=' . $body);
        $el->html('Email');
        $email_button = $el->render();

        $el = null;

        // Previous page.
        /** @var Bootstrap\IconButton $el */
        $el = $this->di->get('IconButton');

        $el->id('summary-previous');
        $el->elementName('a');
        $el->href('#');
        $el->context($btn_class);
        $el->addClass('border-0 ');
        $el->icon('chevron-left');
        $el->tooltip('Go to previous item');
        $prev_button = $el->render();

        $el = null;

        // Next page.
        /** @var Bootstrap\IconButton $el */
        $el = $this->di->get('IconButton');

        $el->id('summary-next');
        $el->elementName('a');
        $el->href('#');
        $el->context($btn_class);
        $el->addClass('border-0 ');
        $el->icon('chevron-right');
        $el->tooltip('Go to next item');
        $next_button = $el->render();

        $el = null;

        $toolbar_class = self::$theme === 'dark' ? 'bg-secondary' : 'bg-darker-5';

        /** @var Bootstrap\Row $el */
        $el = $this->di->get('Row');

        $el->id('bottom-row');
        $el->role('toolbar');
        $el->addClass("px-3 {$toolbar_class}");
        $el->column(
            "<div>$delete_button $export_button $email_button</div><div>$prev_button $next_button</div>",
            'col p-0 my-2 d-flex justify-content-between'
        );
        $bottom_row = $el->render();

        $el = null;

        $this->append(['html' => "$content $bottom_row"]);

        return $this->send();
    }
}
