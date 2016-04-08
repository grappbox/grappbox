#include "LoginController.h"
#include <QDebug>
#include "API/SDataManager.h"

LoginController::LoginController(QWidget *parent)
{
    _IsLoged = false;
}

void LoginController::login(QString name, QString password)
{
    BEGIN_REQUEST;
    {
        SET_ON_DONE("OnLoginSuccess");
        SET_ON_FAIL("OnLoginFailure");
        SET_CALL_OBJECT(this);
        ADD_FIELD("login", name);
        ADD_FIELD("password", password);
        POST(API::DP_USER_DATA, API::PR_LOGIN);
    }
    END_REQUEST;
}

void LoginController::OnLoginSuccess(int id, QByteArray response)
{
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
}

void LoginController::OnLoginFailure(int id, QByteArray response)
{
    _IsLoged = false;
    emit isLogedChanged();
    emit loginFailed();
}

bool LoginController::isLoged()
{
    return _IsLoged;
}
