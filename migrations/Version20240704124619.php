<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240704124619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE activity_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE client_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE due_task_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE interlocutor_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE milestone_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE state_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE task_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE type_of_activity_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE type_of_task_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE activity (id INT NOT NULL, type_of_activity_id INT NOT NULL, client_id INT DEFAULT NULL, billable BOOLEAN DEFAULT NULL, start_date DATE NOT NULL, name VARCHAR(50) DEFAULT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AC74095A578F7A08 ON activity (type_of_activity_id)');
        $this->addSql('CREATE INDEX IDX_AC74095A19EB6921 ON activity (client_id)');
        $this->addSql('COMMENT ON COLUMN activity.start_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('CREATE TABLE assignment (collaborator_id INT NOT NULL, task_id INT NOT NULL, hour_rate DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(collaborator_id, task_id))');
        $this->addSql('CREATE INDEX IDX_30C544BA30098C8C ON assignment (collaborator_id)');
        $this->addSql('CREATE INDEX IDX_30C544BA8DB60186 ON assignment (task_id)');
        $this->addSql('CREATE TABLE client (id INT NOT NULL, siret VARCHAR(14) DEFAULT NULL, siren VARCHAR(9) DEFAULT NULL, adress VARCHAR(255) DEFAULT NULL, social_reason VARCHAR(50) DEFAULT NULL, phone_number VARCHAR(15) DEFAULT NULL, mail VARCHAR(100) DEFAULT NULL, web_site VARCHAR(100) DEFAULT NULL, postal_code VARCHAR(15) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, tvanumber VARCHAR(13) DEFAULT NULL, logo TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE cost (activity_id INT NOT NULL, collaborator_id INT NOT NULL, label VARCHAR(255) NOT NULL, hour_rate DOUBLE PRECISION NOT NULL, PRIMARY KEY(activity_id, collaborator_id))');
        $this->addSql('CREATE INDEX IDX_182694FC81C06096 ON cost (activity_id)');
        $this->addSql('CREATE INDEX IDX_182694FC30098C8C ON cost (collaborator_id)');
        $this->addSql('CREATE TABLE due_task (id INT NOT NULL, collaborator_id INT NOT NULL, task_id INT NOT NULL, date_due_task DATE DEFAULT NULL, start_hour TIME(0) WITHOUT TIME ZONE DEFAULT NULL, end_hour TIME(0) WITHOUT TIME ZONE DEFAULT NULL, comment TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1E934C6E30098C8C8DB60186 ON due_task (collaborator_id, task_id)');
        $this->addSql('COMMENT ON COLUMN due_task.date_due_task IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN due_task.start_hour IS \'(DC2Type:time_immutable)\'');
        $this->addSql('COMMENT ON COLUMN due_task.end_hour IS \'(DC2Type:time_immutable)\'');
        $this->addSql('CREATE TABLE interlocutor (id INT NOT NULL, client_id INT NOT NULL, first_name VARCHAR(50) DEFAULT NULL, last_name VARCHAR(50) DEFAULT NULL, mail VARCHAR(100) DEFAULT NULL, phone_number VARCHAR(15) DEFAULT NULL, job VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F3FE3BF519EB6921 ON interlocutor (client_id)');
        $this->addSql('CREATE TABLE interlocutor_activity (interlocutor_id INT NOT NULL, activity_id INT NOT NULL, PRIMARY KEY(interlocutor_id, activity_id))');
        $this->addSql('CREATE INDEX IDX_2135AFF7B3F944DB ON interlocutor_activity (interlocutor_id)');
        $this->addSql('CREATE INDEX IDX_2135AFF781C06096 ON interlocutor_activity (activity_id)');
        $this->addSql('CREATE TABLE milestone (id INT NOT NULL, activity_id INT NOT NULL, date_end DATE NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4FAC838281C06096 ON milestone (activity_id)');
        $this->addSql('COMMENT ON COLUMN milestone.date_end IS \'(DC2Type:date_immutable)\'');
        $this->addSql('CREATE TABLE state (id INT NOT NULL, label VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE task (id INT NOT NULL, state_id INT NOT NULL, type_of_task_id INT NOT NULL, activity_id INT NOT NULL, milestone_id INT DEFAULT NULL, name VARCHAR(100) DEFAULT NULL, description TEXT DEFAULT NULL, start_date_forecast DATE DEFAULT NULL, end_date_forecast DATE DEFAULT NULL, duration_forecast INT DEFAULT NULL, duration_invoice_real DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_527EDB255D83CC1 ON task (state_id)');
        $this->addSql('CREATE INDEX IDX_527EDB256DC02612 ON task (type_of_task_id)');
        $this->addSql('CREATE INDEX IDX_527EDB2581C06096 ON task (activity_id)');
        $this->addSql('CREATE INDEX IDX_527EDB254B3E2EDA ON task (milestone_id)');
        $this->addSql('COMMENT ON COLUMN task.start_date_forecast IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN task.end_date_forecast IS \'(DC2Type:date_immutable)\'');
        $this->addSql('CREATE TABLE type_of_activity (id INT NOT NULL, label VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE type_of_task (id INT NOT NULL, label VARCHAR(100) NOT NULL, coef_hour_rate DOUBLE PRECISION DEFAULT NULL, color VARCHAR(31) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, mail VARCHAR(180) NOT NULL, roles JSON NOT NULL, first_name VARCHAR(50) DEFAULT NULL, last_name VARCHAR(50) DEFAULT NULL, phone_number VARCHAR(15) DEFAULT NULL, job VARCHAR(50) DEFAULT NULL, hour_rate_by_default DOUBLE PRECISION DEFAULT NULL, avatar TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_MAIL ON "user" (mail)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095A578F7A08 FOREIGN KEY (type_of_activity_id) REFERENCES type_of_activity (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095A19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE assignment ADD CONSTRAINT FK_30C544BA30098C8C FOREIGN KEY (collaborator_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE assignment ADD CONSTRAINT FK_30C544BA8DB60186 FOREIGN KEY (task_id) REFERENCES task (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cost ADD CONSTRAINT FK_182694FC81C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cost ADD CONSTRAINT FK_182694FC30098C8C FOREIGN KEY (collaborator_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE due_task ADD CONSTRAINT FK_1E934C6E30098C8C8DB60186 FOREIGN KEY (collaborator_id, task_id) REFERENCES assignment (collaborator_id, task_id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE interlocutor ADD CONSTRAINT FK_F3FE3BF519EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE interlocutor_activity ADD CONSTRAINT FK_2135AFF7B3F944DB FOREIGN KEY (interlocutor_id) REFERENCES interlocutor (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE interlocutor_activity ADD CONSTRAINT FK_2135AFF781C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE milestone ADD CONSTRAINT FK_4FAC838281C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB255D83CC1 FOREIGN KEY (state_id) REFERENCES state (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB256DC02612 FOREIGN KEY (type_of_task_id) REFERENCES type_of_task (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB2581C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB254B3E2EDA FOREIGN KEY (milestone_id) REFERENCES milestone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE activity_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE client_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE due_task_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE interlocutor_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE milestone_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE state_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE task_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE type_of_activity_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE type_of_task_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('ALTER TABLE activity DROP CONSTRAINT FK_AC74095A578F7A08');
        $this->addSql('ALTER TABLE activity DROP CONSTRAINT FK_AC74095A19EB6921');
        $this->addSql('ALTER TABLE assignment DROP CONSTRAINT FK_30C544BA30098C8C');
        $this->addSql('ALTER TABLE assignment DROP CONSTRAINT FK_30C544BA8DB60186');
        $this->addSql('ALTER TABLE cost DROP CONSTRAINT FK_182694FC81C06096');
        $this->addSql('ALTER TABLE cost DROP CONSTRAINT FK_182694FC30098C8C');
        $this->addSql('ALTER TABLE due_task DROP CONSTRAINT FK_1E934C6E30098C8C8DB60186');
        $this->addSql('ALTER TABLE interlocutor DROP CONSTRAINT FK_F3FE3BF519EB6921');
        $this->addSql('ALTER TABLE interlocutor_activity DROP CONSTRAINT FK_2135AFF7B3F944DB');
        $this->addSql('ALTER TABLE interlocutor_activity DROP CONSTRAINT FK_2135AFF781C06096');
        $this->addSql('ALTER TABLE milestone DROP CONSTRAINT FK_4FAC838281C06096');
        $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_527EDB255D83CC1');
        $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_527EDB256DC02612');
        $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_527EDB2581C06096');
        $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_527EDB254B3E2EDA');
        $this->addSql('DROP TABLE activity');
        $this->addSql('DROP TABLE assignment');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE cost');
        $this->addSql('DROP TABLE due_task');
        $this->addSql('DROP TABLE interlocutor');
        $this->addSql('DROP TABLE interlocutor_activity');
        $this->addSql('DROP TABLE milestone');
        $this->addSql('DROP TABLE state');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE type_of_activity');
        $this->addSql('DROP TABLE type_of_task');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
