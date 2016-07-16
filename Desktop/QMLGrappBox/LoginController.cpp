#include "LoginController.h"
#include <QDebug>
#include <QDateTime>
#include "API/SDataManager.h"

LoginController::LoginController(QWidget *parent)
{
	Q_UNUSED(parent)
    _IsLoged = false;
}

void LoginController::login(QString name, QString password)
{
    BEGIN_REQUEST;
    {
        EPURE_WARNING_INDEX
        SET_ON_DONE("OnLoginSuccess");
        SET_ON_FAIL("OnLoginFailure");
        SET_CALL_OBJECT(this);
        ADD_FIELD("login", name);
        ADD_FIELD("password", password);
        POST(API::DP_USER_DATA, API::PR_LOGIN);
    }
    END_REQUEST;
}

void LoginController::logout()
{
    BEGIN_REQUEST_ADV(this, "OnLogoutSuccess", "OnLogoutFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        GET(API::DP_USER_DATA, API::GR_LOGOUT);
    }
    END_REQUEST;
    _IsLoged = false;
    emit isLogedChanged();
    emit logoutSuccess();
}

void LoginController::OnLoginSuccess(int id, QByteArray response)
{
	Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(response);
    QJsonObject obj = doc.object()["data"].toObject();
    int idUser = obj["id"].toInt();
    QString userName = obj["firstname"].toString();
    QString userLastName = obj["lastname"].toString();
    QString userToken = obj["token"].toString();
    QImage *avatar = new QImage(QImage::fromData(QByteArray::fromBase64(obj["avatar"].toString().toStdString().c_str()), "PNG"));
    API::SDataManager::GetDataManager()->RegisterUserConnected(idUser, userName, userLastName, userToken, avatar);
    emit loginSuccess();
    _IsLoged = true;
    emit isLogedChanged();
    BEGIN_REQUEST;
    {
        SET_CALL_OBJECT(this);
        SET_ON_DONE("OnUserInfoDone");
        SET_ON_FAIL("OnUserInfoFail");
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(idUser);
        GET(API::DP_USER_DATA, API::GR_USER_DATA);
    }
    END_REQUEST;
}

void LoginController::OnLoginFailure(int id, QByteArray response)
{
	Q_UNUSED(id)
	Q_UNUSED(response)
    _IsLoged = false;
    emit isLogedChanged();
    emit loginFailed();
}

void LoginController::OnUserInfoDone(int id, QByteArray response)
{
	Q_UNUSED(id)
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(response);
    QJsonObject obj = doc.object()["data"].toObject();
    UserData *user = API::SDataManager::GetDataManager()->user();
    if (user == nullptr)
        user = new UserData();
    user->setId(API::SDataManager::GetDataManager()->GetUserId());
    user->setFirstName(obj["firstname"].toString());
    user->setLastName(obj["lastname"].toString());
    user->setBirthday(JSON_TO_DATE(obj["birthday"].toString()));
    user->setMail(obj["email"].toString());
    user->setPhone(obj["phone"].toString());
    user->setCountry(obj["country"].toString());
    user->setLinkedin(obj["linkedin"].toString());
    user->setViadeo(obj["viadeo"].toString());
    user->setTwitter(obj["twitter"].toString());
    user->setAvatarDate(JSON_TO_DATETIME(obj["avatar"].toString()));
    API::SDataManager::GetDataManager()->setUser(user);
}

void LoginController::OnUserInfoFail(int id, QByteArray response)
{
	Q_UNUSED(id)
	Q_UNUSED(response)

}

void LoginController::OnLogoutSuccess(int id, QByteArray response)
{
	Q_UNUSED(id)
	Q_UNUSED(response)

}

void LoginController::OnLogoutFail(int id, QByteArray response)
{
	Q_UNUSED(id)
	Q_UNUSED(response)

}

bool LoginController::isLoged()
{
    return _IsLoged;
}
