<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220729131753 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_event_topic DROP FOREIGN KEY FK_595861CB71F7E88B');
        $this->addSql('ALTER TABLE pv_event_topic DROP FOREIGN KEY FK_595861CB1F55203D');
        $this->addSql('DROP INDEX idx_595861cb71f7e88b ON pv_event_topic');
        $this->addSql('CREATE INDEX IDX_D8F6727D71F7E88B ON pv_event_topic (event_id)');
        $this->addSql('DROP INDEX idx_595861cb1f55203d ON pv_event_topic');
        $this->addSql('CREATE INDEX IDX_D8F6727D1F55203D ON pv_event_topic (topic_id)');
        $this->addSql('ALTER TABLE pv_event_topic ADD CONSTRAINT FK_595861CB71F7E88B FOREIGN KEY (event_id) REFERENCES pv_event (id)');
        $this->addSql('ALTER TABLE pv_event_topic ADD CONSTRAINT FK_595861CB1F55203D FOREIGN KEY (topic_id) REFERENCES pv_topic (id)');
        $this->addSql('ALTER TABLE pv_event_language DROP FOREIGN KEY FK_29636E6082F1BAF4');
        $this->addSql('ALTER TABLE pv_event_language DROP FOREIGN KEY FK_29636E6071F7E88B');
        $this->addSql('DROP INDEX idx_29636e6071f7e88b ON pv_event_language');
        $this->addSql('CREATE INDEX IDX_B831233E71F7E88B ON pv_event_language (event_id)');
        $this->addSql('DROP INDEX idx_29636e6082f1baf4 ON pv_event_language');
        $this->addSql('CREATE INDEX IDX_B831233E82F1BAF4 ON pv_event_language (language_id)');
        $this->addSql('ALTER TABLE pv_event_language ADD CONSTRAINT FK_29636E6082F1BAF4 FOREIGN KEY (language_id) REFERENCES pv_language (id)');
        $this->addSql('ALTER TABLE pv_event_language ADD CONSTRAINT FK_29636E6071F7E88B FOREIGN KEY (event_id) REFERENCES pv_event (id)');
        $this->addSql('ALTER TABLE pv_event_location DROP FOREIGN KEY FK_A326961E71F7E88B');
        $this->addSql('ALTER TABLE pv_event_location DROP FOREIGN KEY FK_A326961E64D218E');
        $this->addSql('DROP INDEX idx_a326961e71f7e88b ON pv_event_location');
        $this->addSql('CREATE INDEX IDX_3274DB4071F7E88B ON pv_event_location (event_id)');
        $this->addSql('DROP INDEX idx_a326961e64d218e ON pv_event_location');
        $this->addSql('CREATE INDEX IDX_3274DB4064D218E ON pv_event_location (location_id)');
        $this->addSql('ALTER TABLE pv_event_location ADD CONSTRAINT FK_A326961E71F7E88B FOREIGN KEY (event_id) REFERENCES pv_event (id)');
        $this->addSql('ALTER TABLE pv_event_location ADD CONSTRAINT FK_A326961E64D218E FOREIGN KEY (location_id) REFERENCES pv_location (id)');
        $this->addSql('ALTER TABLE pv_project_topic DROP FOREIGN KEY FK_D1A4D1301F55203D');
        $this->addSql('ALTER TABLE pv_project_topic DROP FOREIGN KEY FK_D1A4D130166D1F9C');
        $this->addSql('DROP INDEX idx_d1a4d130166d1f9c ON pv_project_topic');
        $this->addSql('CREATE INDEX IDX_E785187E166D1F9C ON pv_project_topic (project_id)');
        $this->addSql('DROP INDEX idx_d1a4d1301f55203d ON pv_project_topic');
        $this->addSql('CREATE INDEX IDX_E785187E1F55203D ON pv_project_topic (topic_id)');
        $this->addSql('ALTER TABLE pv_project_topic ADD CONSTRAINT FK_D1A4D1301F55203D FOREIGN KEY (topic_id) REFERENCES pv_topic (id)');
        $this->addSql('ALTER TABLE pv_project_topic ADD CONSTRAINT FK_D1A4D130166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_geographic_region DROP FOREIGN KEY FK_4A06592581314161');
        $this->addSql('ALTER TABLE pv_project_geographic_region DROP FOREIGN KEY FK_4A065925166D1F9C');
        $this->addSql('DROP INDEX idx_4a065925166d1f9c ON pv_project_geographic_region');
        $this->addSql('CREATE INDEX IDX_B404FE88166D1F9C ON pv_project_geographic_region (project_id)');
        $this->addSql('DROP INDEX idx_4a06592581314161 ON pv_project_geographic_region');
        $this->addSql('CREATE INDEX IDX_B404FE8881314161 ON pv_project_geographic_region (geographic_region_id)');
        $this->addSql('ALTER TABLE pv_project_geographic_region ADD CONSTRAINT FK_4A06592581314161 FOREIGN KEY (geographic_region_id) REFERENCES pv_geographic_region (id)');
        $this->addSql('ALTER TABLE pv_project_geographic_region ADD CONSTRAINT FK_4A065925166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_country DROP FOREIGN KEY FK_B5BC657AF92F3E70');
        $this->addSql('ALTER TABLE pv_project_country DROP FOREIGN KEY FK_B5BC657A166D1F9C');
        $this->addSql('DROP INDEX idx_b5bc657a166d1f9c ON pv_project_country');
        $this->addSql('CREATE INDEX IDX_39FE4BC4166D1F9C ON pv_project_country (project_id)');
        $this->addSql('DROP INDEX idx_b5bc657af92f3e70 ON pv_project_country');
        $this->addSql('CREATE INDEX IDX_39FE4BC4F92F3E70 ON pv_project_country (country_id)');
        $this->addSql('ALTER TABLE pv_project_country ADD CONSTRAINT FK_B5BC657AF92F3E70 FOREIGN KEY (country_id) REFERENCES pv_country (id)');
        $this->addSql('ALTER TABLE pv_project_country ADD CONSTRAINT FK_B5BC657A166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_state DROP FOREIGN KEY FK_EF77DDD05D83CC1');
        $this->addSql('ALTER TABLE pv_project_state DROP FOREIGN KEY FK_EF77DDD0166D1F9C');
        $this->addSql('DROP INDEX idx_ef77ddd0166d1f9c ON pv_project_state');
        $this->addSql('CREATE INDEX IDX_D956149E166D1F9C ON pv_project_state (project_id)');
        $this->addSql('DROP INDEX idx_ef77ddd05d83cc1 ON pv_project_state');
        $this->addSql('CREATE INDEX IDX_D956149E5D83CC1 ON pv_project_state (state_id)');
        $this->addSql('ALTER TABLE pv_project_state ADD CONSTRAINT FK_EF77DDD05D83CC1 FOREIGN KEY (state_id) REFERENCES pv_state (id)');
        $this->addSql('ALTER TABLE pv_project_state ADD CONSTRAINT FK_EF77DDD0166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_program DROP FOREIGN KEY FK_7422DB983EB8070A');
        $this->addSql('ALTER TABLE pv_project_program DROP FOREIGN KEY FK_7422DB98166D1F9C');
        $this->addSql('DROP INDEX idx_7422db98166d1f9c ON pv_project_program');
        $this->addSql('CREATE INDEX IDX_F860F526166D1F9C ON pv_project_program (project_id)');
        $this->addSql('DROP INDEX idx_7422db983eb8070a ON pv_project_program');
        $this->addSql('CREATE INDEX IDX_F860F5263EB8070A ON pv_project_program (program_id)');
        $this->addSql('ALTER TABLE pv_project_program ADD CONSTRAINT FK_7422DB983EB8070A FOREIGN KEY (program_id) REFERENCES pv_program (id)');
        $this->addSql('ALTER TABLE pv_project_program ADD CONSTRAINT FK_7422DB98166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_instrument DROP FOREIGN KEY FK_20EA1FEBCF11D9C');
        $this->addSql('ALTER TABLE pv_project_instrument DROP FOREIGN KEY FK_20EA1FEB166D1F9C');
        $this->addSql('DROP INDEX idx_20ea1feb166d1f9c ON pv_project_instrument');
        $this->addSql('CREATE INDEX IDX_7CFFE5ED166D1F9C ON pv_project_instrument (project_id)');
        $this->addSql('DROP INDEX idx_20ea1febcf11d9c ON pv_project_instrument');
        $this->addSql('CREATE INDEX IDX_7CFFE5EDCF11D9C ON pv_project_instrument (instrument_id)');
        $this->addSql('ALTER TABLE pv_project_instrument ADD CONSTRAINT FK_20EA1FEBCF11D9C FOREIGN KEY (instrument_id) REFERENCES pv_instrument (id)');
        $this->addSql('ALTER TABLE pv_project_instrument ADD CONSTRAINT FK_20EA1FEB166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_business_sector DROP FOREIGN KEY FK_FC369572C7F1CE18');
        $this->addSql('ALTER TABLE pv_project_business_sector DROP FOREIGN KEY FK_FC369572166D1F9C');
        $this->addSql('DROP INDEX idx_fc369572166d1f9c ON pv_project_business_sector');
        $this->addSql('CREATE INDEX IDX_964EE5D2166D1F9C ON pv_project_business_sector (project_id)');
        $this->addSql('DROP INDEX idx_fc369572c7f1ce18 ON pv_project_business_sector');
        $this->addSql('CREATE INDEX IDX_964EE5D2C7F1CE18 ON pv_project_business_sector (business_sector_id)');
        $this->addSql('ALTER TABLE pv_project_business_sector ADD CONSTRAINT FK_FC369572C7F1CE18 FOREIGN KEY (business_sector_id) REFERENCES pv_business_sector (id)');
        $this->addSql('ALTER TABLE pv_project_business_sector ADD CONSTRAINT FK_FC369572166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_user CHANGE notifications notifications LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('DROP INDEX uniq_c9e6fed3e7927c74 ON pv_user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BAEBB0C6E7927C74 ON pv_user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pv_event_language DROP FOREIGN KEY FK_B831233E71F7E88B');
        $this->addSql('ALTER TABLE pv_event_language DROP FOREIGN KEY FK_B831233E82F1BAF4');
        $this->addSql('DROP INDEX idx_b831233e71f7e88b ON pv_event_language');
        $this->addSql('CREATE INDEX IDX_29636E6071F7E88B ON pv_event_language (event_id)');
        $this->addSql('DROP INDEX idx_b831233e82f1baf4 ON pv_event_language');
        $this->addSql('CREATE INDEX IDX_29636E6082F1BAF4 ON pv_event_language (language_id)');
        $this->addSql('ALTER TABLE pv_event_language ADD CONSTRAINT FK_B831233E71F7E88B FOREIGN KEY (event_id) REFERENCES pv_event (id)');
        $this->addSql('ALTER TABLE pv_event_language ADD CONSTRAINT FK_B831233E82F1BAF4 FOREIGN KEY (language_id) REFERENCES pv_language (id)');
        $this->addSql('ALTER TABLE pv_event_location DROP FOREIGN KEY FK_3274DB4071F7E88B');
        $this->addSql('ALTER TABLE pv_event_location DROP FOREIGN KEY FK_3274DB4064D218E');
        $this->addSql('DROP INDEX idx_3274db4071f7e88b ON pv_event_location');
        $this->addSql('CREATE INDEX IDX_A326961E71F7E88B ON pv_event_location (event_id)');
        $this->addSql('DROP INDEX idx_3274db4064d218e ON pv_event_location');
        $this->addSql('CREATE INDEX IDX_A326961E64D218E ON pv_event_location (location_id)');
        $this->addSql('ALTER TABLE pv_event_location ADD CONSTRAINT FK_3274DB4071F7E88B FOREIGN KEY (event_id) REFERENCES pv_event (id)');
        $this->addSql('ALTER TABLE pv_event_location ADD CONSTRAINT FK_3274DB4064D218E FOREIGN KEY (location_id) REFERENCES pv_location (id)');
        $this->addSql('ALTER TABLE pv_event_topic DROP FOREIGN KEY FK_D8F6727D71F7E88B');
        $this->addSql('ALTER TABLE pv_event_topic DROP FOREIGN KEY FK_D8F6727D1F55203D');
        $this->addSql('DROP INDEX idx_d8f6727d71f7e88b ON pv_event_topic');
        $this->addSql('CREATE INDEX IDX_595861CB71F7E88B ON pv_event_topic (event_id)');
        $this->addSql('DROP INDEX idx_d8f6727d1f55203d ON pv_event_topic');
        $this->addSql('CREATE INDEX IDX_595861CB1F55203D ON pv_event_topic (topic_id)');
        $this->addSql('ALTER TABLE pv_event_topic ADD CONSTRAINT FK_D8F6727D71F7E88B FOREIGN KEY (event_id) REFERENCES pv_event (id)');
        $this->addSql('ALTER TABLE pv_event_topic ADD CONSTRAINT FK_D8F6727D1F55203D FOREIGN KEY (topic_id) REFERENCES pv_topic (id)');
        $this->addSql('ALTER TABLE pv_project_business_sector DROP FOREIGN KEY FK_964EE5D2166D1F9C');
        $this->addSql('ALTER TABLE pv_project_business_sector DROP FOREIGN KEY FK_964EE5D2C7F1CE18');
        $this->addSql('DROP INDEX idx_964ee5d2166d1f9c ON pv_project_business_sector');
        $this->addSql('CREATE INDEX IDX_FC369572166D1F9C ON pv_project_business_sector (project_id)');
        $this->addSql('DROP INDEX idx_964ee5d2c7f1ce18 ON pv_project_business_sector');
        $this->addSql('CREATE INDEX IDX_FC369572C7F1CE18 ON pv_project_business_sector (business_sector_id)');
        $this->addSql('ALTER TABLE pv_project_business_sector ADD CONSTRAINT FK_964EE5D2166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_business_sector ADD CONSTRAINT FK_964EE5D2C7F1CE18 FOREIGN KEY (business_sector_id) REFERENCES pv_business_sector (id)');
        $this->addSql('ALTER TABLE pv_project_country DROP FOREIGN KEY FK_39FE4BC4166D1F9C');
        $this->addSql('ALTER TABLE pv_project_country DROP FOREIGN KEY FK_39FE4BC4F92F3E70');
        $this->addSql('DROP INDEX idx_39fe4bc4166d1f9c ON pv_project_country');
        $this->addSql('CREATE INDEX IDX_B5BC657A166D1F9C ON pv_project_country (project_id)');
        $this->addSql('DROP INDEX idx_39fe4bc4f92f3e70 ON pv_project_country');
        $this->addSql('CREATE INDEX IDX_B5BC657AF92F3E70 ON pv_project_country (country_id)');
        $this->addSql('ALTER TABLE pv_project_country ADD CONSTRAINT FK_39FE4BC4166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_country ADD CONSTRAINT FK_39FE4BC4F92F3E70 FOREIGN KEY (country_id) REFERENCES pv_country (id)');
        $this->addSql('ALTER TABLE pv_project_geographic_region DROP FOREIGN KEY FK_B404FE88166D1F9C');
        $this->addSql('ALTER TABLE pv_project_geographic_region DROP FOREIGN KEY FK_B404FE8881314161');
        $this->addSql('DROP INDEX idx_b404fe88166d1f9c ON pv_project_geographic_region');
        $this->addSql('CREATE INDEX IDX_4A065925166D1F9C ON pv_project_geographic_region (project_id)');
        $this->addSql('DROP INDEX idx_b404fe8881314161 ON pv_project_geographic_region');
        $this->addSql('CREATE INDEX IDX_4A06592581314161 ON pv_project_geographic_region (geographic_region_id)');
        $this->addSql('ALTER TABLE pv_project_geographic_region ADD CONSTRAINT FK_B404FE88166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_geographic_region ADD CONSTRAINT FK_B404FE8881314161 FOREIGN KEY (geographic_region_id) REFERENCES pv_geographic_region (id)');
        $this->addSql('ALTER TABLE pv_project_instrument DROP FOREIGN KEY FK_7CFFE5ED166D1F9C');
        $this->addSql('ALTER TABLE pv_project_instrument DROP FOREIGN KEY FK_7CFFE5EDCF11D9C');
        $this->addSql('DROP INDEX idx_7cffe5ed166d1f9c ON pv_project_instrument');
        $this->addSql('CREATE INDEX IDX_20EA1FEB166D1F9C ON pv_project_instrument (project_id)');
        $this->addSql('DROP INDEX idx_7cffe5edcf11d9c ON pv_project_instrument');
        $this->addSql('CREATE INDEX IDX_20EA1FEBCF11D9C ON pv_project_instrument (instrument_id)');
        $this->addSql('ALTER TABLE pv_project_instrument ADD CONSTRAINT FK_7CFFE5ED166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_instrument ADD CONSTRAINT FK_7CFFE5EDCF11D9C FOREIGN KEY (instrument_id) REFERENCES pv_instrument (id)');
        $this->addSql('ALTER TABLE pv_project_program DROP FOREIGN KEY FK_F860F526166D1F9C');
        $this->addSql('ALTER TABLE pv_project_program DROP FOREIGN KEY FK_F860F5263EB8070A');
        $this->addSql('DROP INDEX idx_f860f526166d1f9c ON pv_project_program');
        $this->addSql('CREATE INDEX IDX_7422DB98166D1F9C ON pv_project_program (project_id)');
        $this->addSql('DROP INDEX idx_f860f5263eb8070a ON pv_project_program');
        $this->addSql('CREATE INDEX IDX_7422DB983EB8070A ON pv_project_program (program_id)');
        $this->addSql('ALTER TABLE pv_project_program ADD CONSTRAINT FK_F860F526166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_program ADD CONSTRAINT FK_F860F5263EB8070A FOREIGN KEY (program_id) REFERENCES pv_program (id)');
        $this->addSql('ALTER TABLE pv_project_state DROP FOREIGN KEY FK_D956149E166D1F9C');
        $this->addSql('ALTER TABLE pv_project_state DROP FOREIGN KEY FK_D956149E5D83CC1');
        $this->addSql('DROP INDEX idx_d956149e166d1f9c ON pv_project_state');
        $this->addSql('CREATE INDEX IDX_EF77DDD0166D1F9C ON pv_project_state (project_id)');
        $this->addSql('DROP INDEX idx_d956149e5d83cc1 ON pv_project_state');
        $this->addSql('CREATE INDEX IDX_EF77DDD05D83CC1 ON pv_project_state (state_id)');
        $this->addSql('ALTER TABLE pv_project_state ADD CONSTRAINT FK_D956149E166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_state ADD CONSTRAINT FK_D956149E5D83CC1 FOREIGN KEY (state_id) REFERENCES pv_state (id)');
        $this->addSql('ALTER TABLE pv_project_topic DROP FOREIGN KEY FK_E785187E166D1F9C');
        $this->addSql('ALTER TABLE pv_project_topic DROP FOREIGN KEY FK_E785187E1F55203D');
        $this->addSql('DROP INDEX idx_e785187e166d1f9c ON pv_project_topic');
        $this->addSql('CREATE INDEX IDX_D1A4D130166D1F9C ON pv_project_topic (project_id)');
        $this->addSql('DROP INDEX idx_e785187e1f55203d ON pv_project_topic');
        $this->addSql('CREATE INDEX IDX_D1A4D1301F55203D ON pv_project_topic (topic_id)');
        $this->addSql('ALTER TABLE pv_project_topic ADD CONSTRAINT FK_E785187E166D1F9C FOREIGN KEY (project_id) REFERENCES pv_project (id)');
        $this->addSql('ALTER TABLE pv_project_topic ADD CONSTRAINT FK_E785187E1F55203D FOREIGN KEY (topic_id) REFERENCES pv_topic (id)');
        $this->addSql('ALTER TABLE pv_user CHANGE notifications notifications LONGTEXT DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('DROP INDEX uniq_baebb0c6e7927c74 ON pv_user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C9E6FED3E7927C74 ON pv_user (email)');
    }
}
