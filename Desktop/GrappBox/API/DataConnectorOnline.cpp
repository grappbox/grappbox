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
    qDebug() << "Request response : " << req;
    if (request->error())
    {
        qDebug() << request->errorString();
        emit responseAPIFailure(_Request[request], req);
    }
    else
        emit responseAPISuccess(_Request[request], req);
}

int DataConnectorOnline::Post(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure)
{
    QNetworkReply *reply = NULL;
    switch (request)
    {
    case PR_LOGIN:
        reply = Login(data, requestResponseObject, slotSuccess, slotFailure);
        break;
    }
    if (reply == NULL)
        throw QException();
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
        reply = Logout(data, requestResponseObject, slotSuccess, slotFailure);
        break;
    }
    if (reply == NULL)
        throw QException();
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

QNetworkReply *DataConnectorOnline::Login(QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure)
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
    if (requestResponseObject != NULL)
    {
        QObject::connect(this, SIGNAL(responseAPISuccess(int, QByteArray)), requestResponseObject, slotSuccess, Qt::UniqueConnection);
        QObject::connect(this, SIGNAL(responseAPIFailure(int, QByteArray)), requestResponseObject, slotFailure, Qt::UniqueConnection);
    }
    return request;
}

QNetworkReply *DataConnectorOnline::Logout(QVector<QString> &data, QObject *requestResponseObject, const char *slotSuccess, const char *slotFailure)
{
    QString url = URL_API + QString("accountadministration/login");
    for (QVector<QString>::const_iterator it = data.constBegin(); it != data.constEnd(); ++it)
    {
        url += QString("/") + *it;
    }

    QNetworkReply *request = _Manager->get(QNetworkRequest(QUrl(url)));

    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
    if (requestResponseObject != NULL)
    {
        QObject::connect(this, SIGNAL(responseAPISuccess(int, QByteArray)), requestResponseObject, slotSuccess, Qt::UniqueConnection);
        QObject::connect(this, SIGNAL(responseAPIFailure(int, QByteArray)), requestResponseObject, slotFailure, Qt::UniqueConnection);
    }
    return request;
}
