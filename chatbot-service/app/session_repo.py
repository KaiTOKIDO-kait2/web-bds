from __future__ import annotations

import json
from typing import Any, Optional

from sqlalchemy import text
from sqlalchemy.orm import Session

from app.pyd_compat import model_parse, model_to_dict
from app.schemas import SearchFilters


def get_or_create_session(db: Session, public_id: str, user_uid: Optional[int]) -> int:
    row = db.execute(
        text("SELECT id FROM chat_session WHERE public_id = :pid LIMIT 1"),
        {"pid": public_id},
    ).first()
    if row:
        sid = int(row[0])
        if user_uid is not None:
            db.execute(
                text("UPDATE chat_session SET user_uid = COALESCE(user_uid, :u), updated_at = CURRENT_TIMESTAMP WHERE id = :id"),
                {"u": user_uid, "id": sid},
            )
        return sid

    db.execute(
        text("INSERT INTO chat_session (public_id, user_uid) VALUES (:pid, :u)"),
        {"pid": public_id, "u": user_uid},
    )
    row2 = db.execute(text("SELECT LAST_INSERT_ID()")).first()
    return int(row2[0])


def load_context_filters(db: Session, session_pk: int) -> tuple[SearchFilters, list[int]]:
    row = db.execute(
        text(
            "SELECT filters_json, last_property_ids_json FROM chat_context_state WHERE session_id = :sid LIMIT 1"
        ),
        {"sid": session_pk},
    ).mappings().first()
    if not row:
        return SearchFilters(), []

    fj = row["filters_json"]
    lp = row["last_property_ids_json"]
    if isinstance(fj, str):
        fj = json.loads(fj)
    if isinstance(lp, str):
        lp = json.loads(lp)
    if not isinstance(fj, dict):
        fj = {}
    if not isinstance(lp, list):
        lp = []
    try:
        filters = model_parse(SearchFilters, fj)
    except Exception:
        filters = SearchFilters()
    pids = [int(x) for x in lp if str(x).isdigit()][:50]
    return filters, pids


def save_context(
    db: Session,
    session_pk: int,
    filters: SearchFilters,
    last_pids: list[int],
) -> None:
    fj = model_to_dict(filters)
    lp = last_pids[:50]
    db.execute(
        text(
            """
            INSERT INTO chat_context_state (session_id, filters_json, last_property_ids_json)
            VALUES (:sid, :fj, :lp)
            ON DUPLICATE KEY UPDATE
              filters_json = VALUES(filters_json),
              last_property_ids_json = VALUES(last_property_ids_json),
              updated_at = CURRENT_TIMESTAMP
            """
        ),
        {"sid": session_pk, "fj": json.dumps(fj, ensure_ascii=False), "lp": json.dumps(lp)},
    )


def add_message(
    db: Session,
    session_pk: int,
    role: str,
    content: str,
    intent_json: Optional[dict[str, Any]] = None,
) -> None:
    ij = json.dumps(intent_json, ensure_ascii=False) if intent_json is not None else None
    if ij is None:
        db.execute(
            text("INSERT INTO chat_message (session_id, role, content) VALUES (:sid, :role, :c)"),
            {"sid": session_pk, "role": role, "c": content},
        )
    else:
        db.execute(
            text(
                "INSERT INTO chat_message (session_id, role, content, intent_json) "
                "VALUES (:sid, :role, :c, :ij)"
            ),
            {"sid": session_pk, "role": role, "c": content, "ij": ij},
        )


def reset_context(db: Session, session_pk: int) -> None:
    db.execute(text("DELETE FROM chat_message WHERE session_id = :sid"), {"sid": session_pk})
    db.execute(text("DELETE FROM chat_context_state WHERE session_id = :sid"), {"sid": session_pk})
