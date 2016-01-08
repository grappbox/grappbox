#include "BugTagEntity.h"

BugTagEntity::BugTagEntity(int id, QString name)
{
    _id = id;
    _name = name;
}

bool BugTagEntity::operator==(const BugTagEntity &tag)
{
    return _id = tag._id;
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
