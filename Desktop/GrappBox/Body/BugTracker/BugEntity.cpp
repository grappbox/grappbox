#include "BugEntity.h"

SBugState::SBugState(const int id, const QString &name)
{
    _id = id;
    _name = name;
}

SBugState::SBugState() : SBugState(-1, "") {}
SBugState::~SBugState() {}

//System
BugEntity::BugEntity()
{
    _id = -1;
}

BugEntity::BugEntity(QJsonObject obj)
{
    _id = obj["id"].toInt();
    _authorId = obj["creatorId"].toInt();
    _projectId = obj["projectId"].toInt();
    _title = obj["title"].toString();
    _description = obj["description"].toString();
    _parentId = obj["parentId"].toInt();
    if (!obj["createdAt"].isNull())
        _createdAt = QDateTime::fromString(obj["createdAt"].toObject()["date"].toString(), "yyyy-MM-dd hh:mm:ss");
    if (!obj["editedAt"].isNull())
        _editedAt = QDateTime::fromString(obj["editedAt"].toObject()["date"].toString(), "yyyy-MM-dd hh:mm:ss");
    if (!obj["deletedAt"].isNull())
        _deletedAt = QDateTime::fromString(obj["deletedAt"].toObject()["date"].toString(), "yyyy-MM-dd hh:mm:ss");
    _state = SBugState(obj["state"].toObject()["id"].toInt(), obj["state"].toObject()["name"].toString());
    if (!obj["tags"].isNull())
    {
        QJsonArray tags = obj["tags"].toArray();
        QJsonArray::iterator tagIt;

        for (tagIt = tags.begin(); tagIt != tags.end(); ++tagIt)
            _tags.append(BugTagEntity((*tagIt).toObject()["id"].toInt(), (*tagIt).toObject()["name"].toString()));
    }
    if (!obj["users"].isNull())

    {
        QJsonArray users = obj["users"].toArray();
        QJsonArray::iterator userIt;

        for (userIt = users.begin(); userIt != users.end(); ++userIt)
        {
            QJsonObject current = (*userIt).toObject();

            _users.append(BugUser(current["id"].toInt(), current["name"].toString(), current["email"].toString(), QByteArray::fromBase64(current["avatar"].toString().toStdString().c_str())));
        }
    }
}

//Getters
const int BugEntity::GetID() const { return _id; }
const int BugEntity::GetAuthorId() const { return _authorId; }
const int BugEntity::GetProjectId() const { return _projectId; }
const QString &BugEntity::GetTitle() const { return _title; }
const QString &BugEntity::GetDescription() const { return _description; }
const int BugEntity::GetParentId() const { return _parentId; }
const QDateTime &BugEntity::GetCreatedAt() const { return _createdAt; }
const QDateTime &BugEntity::GetEditedAt() const { return _editedAt; }
const QDateTime &BugEntity::GetDeletedAt() const { return _deletedAt; }
const SBugState &BugEntity::GetState() const { return _state; }
const QList<BugTagEntity> &BugEntity::GetTags() const { return _tags; }
const QList<BugUser> &BugEntity::GetUsers() const { return _users; }
const bool BugEntity::IsValid() const { return _id >= 0; }

//Setters
void BugEntity::SetAuthorID(const int id) { _id = id; }
void BugEntity::SetProjectID(const int id) { _projectId = id; }
void BugEntity::SetTitle(const QString &title) { _title = title; }
void BugEntity::SetDescription(const QString &desc) { _description = desc; }
void BugEntity::SetParentID(const int id) { _parentId = id; }
void BugEntity::SetState(const SBugState &state) { _state = state; }
void BugEntity::SetTags(const QList<BugTagEntity> &tags) { _tags = tags; }
void BugEntity::AddTag(const BugTagEntity &tag) { _tags.append(tag); }
void BugEntity::DelTag(QList<BugTagEntity>::const_iterator tagIt) { _tags.removeOne(*tagIt); }
void BugEntity::SetUsers(const QList<BugUser> &users) { _users = users; }
void BugEntity::AddUser(const BugUser &user) { _users.append(user); }
void BugEntity::DelUser(QList<BugUser>::const_iterator userIt) { _users.removeOne(*userIt); }
