<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Internal_Linking_Seo extends Module
{
    public function __construct()
    {
        $this->name = 'internal_linking_seo';
        $this->tab = 'seo';
        $this->version = '1.0.0';
        $this->author = 'Professional Dev';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7.8.0', 'max' => '9.9.9.9');
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Internal Linking SEO');
        $this->description = $this->l('Module to manage internal linking for SEO on your PrestaShop store.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('displayFooterProduct') &&
            $this->registerHook('displayFooterCategory') &&
            $this->installDb();
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->uninstallDb();
    }

    protected function installDb()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."internal_linking_rules` (
            `id_rule` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `keyword` VARCHAR(255) NOT NULL,
            `link` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id_rule`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;";
        return Db::getInstance()->execute($sql);
    }

    protected function uninstallDb()
    {
        $sql = "DROP TABLE IF EXISTS `"._DB_PREFIX_."internal_linking_rules`;";
        return Db::getInstance()->execute($sql);
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitInternalLinkingSeo')) {
            $keyword = Tools::getValue('keyword');
            $link = Tools::getValue('link');
            if ($keyword && $link) {
                Db::getInstance()->insert('internal_linking_rules', [
                    'keyword' => pSQL($keyword),
                    'link' => pSQL($link),
                ]);
                $output .= $this->displayConfirmation($this->l('Rule added successfully'));
            } else {
                $output .= $this->displayError($this->l('Please fill all fields'));
            }
        }
        $output .= $this->renderForm();
        $output .= $this->renderRulesList();
        return $output;
    }

    protected function renderForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Add Internal Linking Rule'),
                    'icon' => 'icon-link',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Keyword'),
                        'name' => 'keyword',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Link URL'),
                        'name' => 'link',
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Add Rule'),
                    'class' => 'btn btn-primary',
                    'name' => 'submitInternalLinkingSeo',
                ],
            ],
        ];

        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        $helper->title = $this->displayName;
        $helper->show_cancel_button = false;
        $helper->submit_action = 'submitInternalLinkingSeo';

        $helper->fields_value['keyword'] = '';
        $helper->fields_value['link'] = '';

        return $helper->generateForm([$fields_form]);
    }

    protected function renderRulesList()
    {
        $rules = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'internal_linking_rules`');

        $html = '<h3>'.$this->l('Existing Rules').'</h3>';
        if (!$rules) {
            $html .= '<p>'.$this->l('No rules found.').'</p>';
            return $html;
        }

        $html .= '<table class="table">';
        $html .= '<thead><tr><th>'.$this->l('ID').'</th><th>'.$this->l('Keyword').'</th><th>'.$this->l('Link URL').'</th></tr></thead><tbody>';
        foreach ($rules as $rule) {
            $html .= '<tr><td>'.(int)$rule['id_rule'].'</td><td>'.htmlspecialchars($rule['keyword']).'</td><td>'.htmlspecialchars($rule['link']).'</td></tr>';
        }
        $html .= '</tbody></table>';

        return $html;
    }

    public function hookDisplayFooterProduct($params)
    {
        return $this->processInternalLinks($params['product']->description);
    }

    public function hookDisplayFooterCategory($params)
    {
        return $this->processInternalLinks($params['category']->description);
    }

    protected function processInternalLinks($content)
    {
        $rules = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'internal_linking_rules`');
        if (!$rules) {
            return '';
        }

        foreach ($rules as $rule) {
            $keyword = preg_quote($rule['keyword'], '/');
            $link = $rule['link'];
            $replacement = '<a href="'.htmlspecialchars($link).'" title="'.htmlspecialchars($rule['keyword']).'">'.htmlspecialchars($rule['keyword']).'</a>';
            // Replace first occurrence only
            $content = preg_replace('/\b'.$keyword.'\b/i', $replacement, $content, 1);
        }

        return $content;
    }
}
