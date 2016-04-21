#include "UserData.h"

UserData::UserData() : QObject(nullptr)
{
    _Id = -1;
    _FirstName = "None";
    _LastName = "None";
}

UserData::UserData(int id, QString firstName, QString lastName) : QObject(nullptr)
{
    _Id = id;
    _FirstName = firstName;
    _LastName = lastName;
}


