#include "UserData.h"

UserData::UserData() : QObject(nullptr)
{
    m_id = -1;
    m_roleId = -1;
}

UserData::UserData(int id, QString firstName, QString lastName) : QObject(nullptr)
{
}


