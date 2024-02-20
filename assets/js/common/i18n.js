import { createI18n } from 'vue-i18n';
import de from '../../../translations/messages+intl-icu.de.yaml';
import fr from '../../../translations/messages+intl-icu.fr.yaml';
import it from '../../../translations/messages+intl-icu.it.yaml';

const i18n = createI18n({
    locale: 'de',
    fallbackLocale: 'de',
    messages: {
        de,
        fr,
        it,
    },
})

export default i18n;