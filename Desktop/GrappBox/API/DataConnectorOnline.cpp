#include <QtWidgets/QMessageBox>
#include <QException>
#include <QtNetwork/QNetworkRequest>
#include "DataConnectorOnline.h"
#include <QDebug>

using namespace API;

DataConnectorOnline::DataConnectorOnline()
{
    _Manager = new QNetworkAccessManager();
}

void DataConnectorOnline::OnResponseAPI()
{
    QNetworkReply *request = dynamic_cast<QNetworkReply*>(QObject::sender());
    if (request == NULL || !_Request.contains(request))
    {
        QMessageBox::critical(NULL, "Critical error", "Unable to cast the reply of the API response.", QMessageBox::Ok);
    }
    QByteArray req = request->readAll();
    if (request->error())
    {
        qDebug() << request->errorString();
        QMetaObject::invokeMethod(_CallBack[request]._Request, _CallBack[request]._SlotFailure, Q_ARG(int, _Request[request]), Q_ARG(QByteArray, req));
    }
    else
        QMetaObject::invokeMethod(_CallBack[request]._Request, _CallBack[request]._SlotSuccess, Q_ARG(int, _Request[request]), Q_ARG(QByteArray, req));
}

int DataConnectorOnline::Post(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure)
{
    QNetworkReply *reply = NULL;
    switch (request)
    {
    case PR_LOGIN:
        reply = Login(data);
        break;
    }
    if (reply == NULL)
        throw QException();
    _CallBack[reply] = DataConnectorCallback();
    _CallBack[reply]._Request = requestResponseObject;
    _CallBack[reply]._SlotFailure = slotFailure;
    _CallBack[reply]._SlotSuccess = slotSuccess;
    int maxInt = 1;
    for (QMap<QNetworkReply*,int>::const_iterator it = _Request.constBegin(); it != _Request.constEnd(); ++it)
    {
        if (it.value() >= maxInt)
        {
            maxInt = it.value() + 1;
        }
    }
    _Request[reply] = maxInt;
    return maxInt;
}

int DataConnectorOnline::Get(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure)
{
    QNetworkReply *reply = NULL;
    switch (request)
    {
    case GR_LOGOUT:
        reply = Logout(data);
        break;

    case GR_LIST_PROJECT:
        reply = GetAction("dashboard/getprojectsglobalprogress", data);
        break;

    case GR_PROJECT:
        reply = GetAction("dashboard/getprojectbasicinformations", data);
        break;

    case GR_CREATOR_PROJECT:
        reply = GetAction("dashboard/getprojectcreator", data);
        break;

    case GR_LIST_MEMBER_PROJECT:
        reply = GetAction("dashboard/getteamoccupation", data);
        break;

    case GR_LIST_MEETING:
        reply = GetAction("dashboard/getnextmeetings", data);
        break;
    }
    if (reply == NULL)
        throw QException();
    _CallBack[reply] = DataConnectorCallback();
    _CallBack[reply]._Request = requestResponseObject;
    _CallBack[reply]._SlotFailure = slotFailure;
    _CallBack[reply]._SlotSuccess = slotSuccess;
    int maxInt = 1;
    for (QMap<QNetworkReply*,int>::const_iterator it = _Request.constBegin(); it != _Request.constEnd(); ++it)
    {
        if (it.value() >= maxInt)
        {
            maxInt = it.value() + 1;
        }
    }
    _Request[reply] = maxInt;
    return maxInt;
}

int DataConnectorOnline::Delete(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure)
{

}

QNetworkReply *DataConnectorOnline::Login(QVector<QString> &data)
{
    QJsonObject json;
    json["login"] = data[0];
    json["password"] = data[1];
    QJsonDocument doc(json);
    QByteArray jsonba = doc.toJson(QJsonDocument::Compact);

    QNetworkRequest requestSend(QUrl(URL_API + QString("accountadministration/login")));
    requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
    requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
    QNetworkReply *request = _Manager->post(requestSend, jsonba);
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
    return request;
}

QNetworkReply *DataConnectorOnline::Logout(QVector<QString> &data)
{
    QString url = URL_API + QString("accountadministration/logout");
    for (QVector<QString>::const_iterator it = data.constBegin(); it != data.constEnd(); ++it)
    {
        url += QString("/") + *it;
    }

    QNetworkReply *request = _Manager->get(QNetworkRequest(QUrl(url)));

    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));

    return request;
}

QNetworkReply *DataConnectorOnline::GetAction(QString urlIn, QVector<QString> &data)
{
    QString url = URL_API + urlIn;
    for (QVector<QString>::const_iterator it = data.constBegin(); it != data.constEnd(); ++it)
    {
        url += QString("/") + *it;
    }
    QNetworkReply *request = _Manager->get(QNetworkRequest(QUrl(url)));

    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));

    return request;
}
