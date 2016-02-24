#ifndef REQUESTVIEW_H
#define REQUESTVIEW_H

#include <QWidget>
#include <QMap>
#include <QLabel>
#include <QListWidget>
#include <QFormLayout>
#include <QElapsedTimer>
#include <QNetWorkReply>
#include <QNetworkRequest>
#include <QJsonArray>
#include <QJsonDocument>
#include <QJsonObject>
#include <QTimer>
#include <QTime>
#include <QByteArray>
#include <QTextEdit>

struct RequestDebug
{
    QElapsedTimer _Timer;
    int _Millisecond;
    QByteArray _DataIn;
    QByteArray _DataOut;
    QString _Url;
    QString _ErrorCode;
    QString _ErrorMessage;
    QListWidgetItem *_Item;
};

class RequestView : public QWidget
{
    Q_OBJECT
public:
    RequestView(RequestDebug *data);

public slots:
    void Update();

private:
    RequestDebug *_Data;

    QLabel *_Time;

    QTimer _TimerUpdate;
};
#endif // REQUESTVIEW_H
