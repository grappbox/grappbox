#include <QMessageBox>
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
    if (request->error())
        emit responseAPIFailure(_Request[request], request->readAll());
    else
        emit responseAPISuccess(_Request[request], request->readAll());
}

int DataConnectorOnline::Post(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure)
{
    QNetworkReply *reply = NULL;
    switch (request)
    {
    case PR_LOGIN:
        qDebug() << "Send login...";
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

    qDebug() << "Document to send : " << QString(jsonba);
    QNetworkRequest requestSend(QUrl(URL_API + QString("accountadministration/login")));
    qDebug() << "URL : " << requestSend.url();
    requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
    requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
    QNetworkReply *request = _Manager->post(requestSend, jsonba);
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
    QObject::connect(this, SIGNAL(responseAPISuccess(int, QByteArray)), requestResponseObject, slotSuccess, Qt::UniqueConnection);
    QObject::connect(this, SIGNAL(responseAPIFailure(int, QByteArray)), requestResponseObject, slotFailure, Qt::UniqueConnection);
    return request;
}
