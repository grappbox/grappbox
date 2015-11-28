#ifndef DATACONNECTORONLINE_H
#define DATACONNECTORONLINE_H

#include <QtNetwork/QNetworkAccessManager>
#include <QtNetwork/QNetworkReply>
#include <QJSONObject>
#include <QJsonDocument>
#include <QMap>

#include "IDataConnector.h"

#define URL_API QString("http://api.grappbox.com/app_dev.php/V0.6/")

namespace API
{
    class DataConnectorOnline : public QObject, public IDataConnector
    {
        Q_OBJECT
    public:
        DataConnectorOnline();

        virtual int Post(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure);
        virtual int Get(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure);
        virtual int Delete(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure);

    signals:
        void responseAPISuccess(int, QByteArray);
        void responseAPIFailure(int, QByteArray);

    public slots:
        void OnResponseAPI();

    private:
        QMap<QNetworkReply*, int> _Request;
        QNetworkAccessManager *_Manager;

        // Post
    private:
        QNetworkReply *Login(QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure);

        // Get
    private:
        QNetworkReply *Logout(QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure);
        QNetworkReply *GetAllProjectsOfUser(QVector<QString> &data, QObject *requestResponseObject, const char *slotSuccess, const char *slotFailure);
        QNetworkReply *GetAllMeetingOfUser(QVector<QString> &data, QObject *requestResponseObject, const char *slotSuccess, const char *slotFailure);
        QNetworkReply *GetAllManagedUsersOfUser(QVector<QString> &data, QObject *requestResponseObject, const char *slotSuccess, const char *slotFailure);
    };

}

#endif // DATACONNECTORONLINE_H
