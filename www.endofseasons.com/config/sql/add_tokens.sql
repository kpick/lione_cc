ALTER TABLE players ADD event_token_3_day INT default 0;
ALTER TABLE players ADD event_token_4_day INT default 0;
ALTER TABLE players ADD event_token_1_day INT default 0;

ALTER TABLE players ADD food_token_3_day INT default 0;
ALTER TABLE players ADD food_token_4_day INT default 0;
ALTER TABLE players ADD food_token_1_day INT default 0;

ALTER TABLE events_players ADD food BOOLEAN default false;

ALTER TABLE transactions_skus ADD player_id INT default 0;