
#include "UserModel.h"

UserModel::UserModel(QObject *parent) : QObject(parent)
{
    m_isLoading = false;
}

void UserModel::getUserModel()
{
    BEGIN_REQUEST;
    {
        SET_CALL_OBJECT(this);
        SET_ON_DONE("onGetUserDone");
        SET_ON_FAIL("onGetUserFail");
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(API::SDataManager::GetDataManager()->user()->id());
        GET(API::DP_USER_DATA, API::GR_USER_DATA);
    }
    END_REQUEST;
    m_isLoading = true;
    emit isLoadingChanged(true);
}

void UserModel::setUserModel(UserData *user, QString oldPassword, QString newPassword)
{
    BEGIN_REQUEST;
    {
        SET_CALL_OBJECT(this);
        SET_ON_DONE("onSetUserDone");
        SET_ON_FAIL("onSetUserFail");
        ADD_URL_FIELD(USER_TOKEN);
        ADD_FIELD("firstname", user->firstName());
        ADD_FIELD("lastname", user->lastName());
        ADD_FIELD("birthday", user->birthday().toString("yyyy-MM-dd"));
        if (oldPassword != "" && newPassword != "")
        {
            // Add here the old password.
            ADD_FIELD("password", newPassword);
        }
        ADD_FIELD("phone", user->phone());
        ADD_FIELD("country", user->country());
        ADD_FIELD("linkedin", user->linkedin());
        ADD_FIELD("twitter", user->twitter());
        ADD_FIELD("viadeo", user->viadeo());
        PUT(API::DP_USER_DATA, API::PUTR_USERSETTINGS);
    }
    END_REQUEST;
    m_isLoading = true;
    emit isLoadingChanged(true);
}

bool UserModel::isLoading()
{
    return m_isLoading;
}

void UserModel::onGetUserDone(int id, QByteArray data)
{
	Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    UserData *user = API::SDataManager::GetDataManager()->user();
    if (user == nullptr)
        user = new UserData();
    user->setId(API::SDataManager::GetDataManager()->GetUserId());
    user->setFirstName(obj["firstname"].toString());
    user->setLastName(obj["lastname"].toString());
    user->setBirthday(JSON_TO_DATE(obj["birthday"].toString()));
    user->setMail(obj["mail"].toString());
    user->setPhone(obj["phone"].toString());
    user->setCountry(obj["country"].toString());
    user->setLinkedin(obj["linkedin"].toString());
    user->setViadeo(obj["viadeo"].toString());
    user->setTwitter(obj["twitter"].toString());
    API::SDataManager::GetDataManager()->setUser(user);
    m_isLoading = false;
    emit userChangedSuccess();
    emit isLoadingChanged(false);
}

void UserModel::onGetUserFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    emit error("User", "Unable to retrive user information. Please try again later.");
    m_isLoading = false;
    emit isLoadingChanged(false);
}

void UserModel::onSetUserDone(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    getUserModel();
}

void UserModel::onSetUserFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    emit error("User", "Unable to change user information. Please try again later.");
    m_isLoading = false;
    emit isLoadingChanged(false);
}



