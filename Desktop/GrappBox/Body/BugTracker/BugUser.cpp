#include "BugUser.h"

BugUser::BugUser() : BugUser(-1, "", "", QByteArray(""))
{

}

BugUser::BugUser(const int id, const QString &name, const QString &email, const QByteArray &avatar)
{
    _id = id;
    _name = name;
    _email = email;
    _avatar = QImage::fromData(avatar, "PNG");
}

bool BugUser::operator ==(const BugUser &user)
{
    return this->_id == user._id;
}

const int BugUser::GetId() const { return _id; }
const QString &BugUser::GetName() const { return _name; }
const QString &BugUser::GetEmail() const { return _email; }
const QImage &BugUser::GetAvatar() const { return _avatar; }

void BugUser::SetName(const QString &name) { _name = name; }
void BugUser::SetEmail(const QString &email) { _email = email; }
void BugUser::SetAvatar(const QByteArray &avatar) { _avatar = QImage::fromData(avatar, "PNG"); }
