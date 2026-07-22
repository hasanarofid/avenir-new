const fs = require('fs');
const path = require('path');

const publicPages = [
    'Home.vue',
    'About.vue',
    'Artikel.vue',
    'ArtikelDetail.vue',
    'News.vue',
    'NewsDetail.vue',
    'KatalogDetail.vue',
    'Partners.vue',
    'Welcome.vue',
    'Subscription.vue'
];

const pagesDir = path.join(__dirname, 'resources/js/Pages');

const seoTemplate = (pageName) => `    <Head>
        <title>${pageName.replace('.vue', '')} | AVENIR</title>
        <meta name="description" content="AVENIR - Platform riset dan direktori pasar modal Indonesia yang komprehensif." />
        <meta property="og:title" content="${pageName.replace('.vue', '')} | AVENIR" />
        <meta property="og:description" content="AVENIR - Platform riset dan direktori pasar modal Indonesia yang komprehensif." />
        <meta property="og:type" content="website" />
        <meta name="twitter:card" content="summary_large_image" />
        
        <!-- GEO Tags -->
        <meta name="geo.region" content="ID" />
        <meta name="geo.placename" content="Indonesia" />
        <meta name="geo.position" content="-0.789275;113.921327" />
        <meta name="ICBM" content="-0.789275, 113.921327" />
        <meta name="language" content="id-ID" />
        <meta name="view-transition" content="same-origin" />
    </Head>
`;

publicPages.forEach(page => {
    const filePath = path.join(pagesDir, page);
    if (!fs.existsSync(filePath)) {
        console.log(`Skipping ${page}, not found.`);
        return;
    }

    let content = fs.readFileSync(filePath, 'utf8');

    // Make sure <Head> is imported
    if (!content.includes("import { Head")) {
        if (content.includes("import { Link")) {
            content = content.replace("import { Link", "import { Head, Link");
        } else if (content.includes("@inertiajs/vue3")) {
            content = content.replace(/import\s+{([^}]+)}\s+from\s+['"]@inertiajs\/vue3['"];/, (match, p1) => {
                return `import { Head, ${p1.trim()} } from '@inertiajs/vue3';`;
            });
        } else {
            // just append to the top of script block
            content = content.replace("<script setup>", "<script setup>\nimport { Head } from '@inertiajs/vue3';");
        }
    }

    // Insert <Head> into template if it doesn't exist
    if (!content.includes("<Head>")) {
        content = content.replace(/<template>/, "<template>\n" + seoTemplate(page));
        fs.writeFileSync(filePath, content);
        console.log(`Updated ${page}`);
    } else {
        console.log(`Skipping ${page}, already has <Head>`);
    }
});
