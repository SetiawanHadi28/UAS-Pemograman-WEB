<?php
/**
 * get_tools_from_skills()
 * 
 * Mendeteksi kategori tools secara otomatis berdasarkan daftar keahlian.
 * Mengembalikan array [ ['icon'=>'🔥', 'label'=>'...', 'items'=>[...]], ... ]
 */
function get_tools_from_skills(array $skills): array {
    // Normalisasi: lowercase semua skill untuk pencocokan
    $skills_lower = array_map('mb_strtolower', $skills);
    $joined = implode(' ', $skills_lower);

    $tools = [];

    // ── Cyber Security ───────────────────────────────────────────
    $cyber_keywords = ['penetration', 'wireshark', 'metasploit', 'nmap', 'burpsuite',
                       'kali', 'security', 'firewall', 'ids', 'ips', 'ceh', 'comptia',
                       'forensik', 'malware', 'exploit', 'ctf', 'keamanan', 'siem',
                       'splunk', 'snort', 'ossec', 'vpn', 'honeypot'];
    if (_has_keyword($joined, $cyber_keywords)) {
        $tools[] = [
            'icon'  => '🛡️',
            'label' => 'Cyber Security Tools',
            'items' => ['Wireshark · Metasploit', 'Nmap · Burp Suite', 'Kali Linux · John the Ripper', 'OWASP ZAP · SQLmap'],
        ];
    }

    // ── Web Development ──────────────────────────────────────────
    $web_keywords = ['php', 'javascript', 'html', 'css', 'laravel', 'react', 'vue',
                     'angular', 'node', 'bootstrap', 'jquery', 'codeigniter',
                     'web', 'frontend', 'backend', 'fullstack'];
    if (_has_keyword($joined, $web_keywords)) {
        $tools[] = [
            'icon'  => '🌐',
            'label' => 'Web Development',
            'items' => ['PHP · Laravel · Node.js', 'React · Vue · JavaScript', 'HTML5 · CSS3 · Bootstrap', 'REST API · GraphQL'],
        ];
    }

    // ── Database ─────────────────────────────────────────────────
    $db_keywords = ['mysql', 'database', 'postgresql', 'mongodb', 'redis',
                    'sqlite', 'basis data', 'sql', 'nosql'];
    if (_has_keyword($joined, $db_keywords)) {
        $tools[] = [
            'icon'  => '🗄️',
            'label' => 'Database',
            'items' => ['MySQL · PostgreSQL', 'MongoDB · Redis', 'SQL · NoSQL'],
        ];
    }

    // ── Programming / Data Science ───────────────────────────────
    $prog_keywords = ['python', 'java', 'c++', 'golang', 'rust', 'kotlin',
                      'machine learning', 'ai', 'data science', 'tensorflow',
                      'pytorch', 'pandas', 'numpy', 'r studio'];
    if (_has_keyword($joined, $prog_keywords)) {
        $tools[] = [
            'icon'  => '🐍',
            'label' => 'Programming & Data',
            'items' => ['Python · Java · C++', 'TensorFlow · PyTorch', 'Pandas · NumPy · Scikit-learn', 'Jupyter · R Studio'],
        ];
    }

    // ── Networking / Infrastructure ──────────────────────────────
    $net_keywords = ['jaringan', 'network', 'cisco', 'mikrotik', 'linux',
                     'docker', 'kubernetes', 'devops', 'cloud', 'aws',
                     'azure', 'gcp', 'ansible', 'terraform'];
    if (_has_keyword($joined, $net_keywords)) {
        $tools[] = [
            'icon'  => '🌐',
            'label' => 'Network & Infrastructure',
            'items' => ['Cisco · MikroTik · Linux', 'Docker · Kubernetes', 'AWS · Azure · GCP', 'Ansible · Terraform'],
        ];
    }

    // ── UI/UX & Design ───────────────────────────────────────────
    $design_keywords = ['figma', 'adobe', 'ui', 'ux', 'design', 'photoshop',
                        'illustrator', 'sketch', 'canva'];
    if (_has_keyword($joined, $design_keywords)) {
        $tools[] = [
            'icon'  => '🎨',
            'label' => 'Design & UI/UX',
            'items' => ['Figma · Adobe XD', 'Photoshop · Illustrator', 'Canva · Sketch'],
        ];
    }

    // ── Dev Tools (selalu ditampilkan jika ada tools lain) ───────
    if (!empty($tools)) {
        $tools[] = [
            'icon'  => '⚙️',
            'label' => 'Developer Tools',
            'items' => ['Git · GitHub · GitLab', 'VS Code · JetBrains IDE', 'Postman · Insomnia', 'Linux Terminal'],
        ];
    }

    // ── Fallback jika tidak ada yang cocok ───────────────────────
    if (empty($tools)) {
        $tools[] = [
            'icon'  => '🛠️',
            'label' => 'Tools & Teknologi',
            'items' => ['Microsoft Office Suite', 'Google Workspace', 'Notion · Trello · Slack', 'Git · GitHub'],
        ];
    }

    return $tools;
}

/**
 * Helper: cek apakah $haystack mengandung salah satu dari $keywords
 */
function _has_keyword(string $haystack, array $keywords): bool {
    foreach ($keywords as $kw) {
        if (str_contains($haystack, $kw)) return true;
    }
    return false;
}
