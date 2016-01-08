#include "BugTagEntity.h"

BugTagEntity::BugTagEntity(int id, QString name)
{
    _id = id;
    _name = name;
}

const int BugTagEntity::GetID() const
{
    return _id;
}

const QString &BugTagEntity::GetName() const
{
    return _name;
}

void BugTagEntity::SetID(const int id)
{
    _id = id;
}

void BugTagEntity::SetName(const QString &name)
{
    _name = name;
}
