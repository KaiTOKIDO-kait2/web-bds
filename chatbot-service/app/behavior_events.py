from __future__ import annotations

import json
from typing import Any, Optional

from sqlalchemy import text
from sqlalchemy.engine import Engine
from sqlalchemy.orm import Session


EVENT_TABLE_SQL = """
CREATE TABLE IF NOT EXISTS chatbot_event (
  id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_uid INT NULL,
  public_session_id VARCHAR(80) NULL,
  property_id INT NOT NULL,
  event_type VARCHAR(60) NOT NULL,
  source VARCHAR(60) NOT NULL DEFAULT 'chatbot',
  metadata_json LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_chatbot_event_user (user_uid, created_at),
  KEY idx_chatbot_event_property (property_id, created_at),
  KEY idx_chatbot_event_type (event_type, created_at),
  CONSTRAINT fk_chatbot_event_property
    FOREIGN KEY (property_id) REFERENCES property(pid)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
"""


ALLOWED_EVENTS = {
    "chat_result_impression",
    "chat_result_click",
    "property_detail_view",
    "favorite_from_chat",
}


def ensure_event_table(bind: Engine | Session) -> None:
    if isinstance(bind, Session):
        bind.execute(text(EVENT_TABLE_SQL))
        return
    with bind.begin() as conn:
        conn.execute(text(EVENT_TABLE_SQL))


def log_behavior_event(
    db: Session,
    *,
    event_type: str,
    property_id: int,
    user_uid: Optional[int] = None,
    public_session_id: Optional[str] = None,
    source: str = "chatbot",
    metadata: Optional[dict[str, Any]] = None,
) -> None:
    if event_type not in ALLOWED_EVENTS:
        return
    ensure_event_table(db)
    db.execute(
        text(
            """
            INSERT INTO chatbot_event
              (user_uid, public_session_id, property_id, event_type, source, metadata_json)
            VALUES (:user_uid, :public_session_id, :property_id, :event_type, :source, :metadata_json)
            """
        ),
        {
            "user_uid": user_uid,
            "public_session_id": public_session_id,
            "property_id": int(property_id),
            "event_type": event_type,
            "source": (source or "chatbot")[:60],
            "metadata_json": json.dumps(metadata or {}, ensure_ascii=False),
        },
    )
