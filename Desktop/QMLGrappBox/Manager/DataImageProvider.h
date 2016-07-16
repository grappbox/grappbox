#ifndef DATAIMAGEPROVIDER_H
#define DATAIMAGEPROVIDER_H

#include <QQuickImageProvider>
#include <QMap>
#include <QDateTime>

#include "Manager/SInfoManager.h"

#include "API/SDataManager.h"

struct DataImage
{
    bool isLoaded;
    bool isDeprectated;
    bool isWaiting;
    QDateTime time;
    QPixmap pixmap;

    DataImage(QPixmap __pixmap)
    {
        pixmap = __pixmap;
    }
    DataImage() {}
};

class DataImageProvider : public QObject, public QQuickImageProvider
{
    Q_OBJECT

public:
    static DataImageProvider *getInstance();
    QPixmap requestPixmap(const QString &id, QSize *size, const QSize &requestedSize);

public:
    Q_INVOKABLE bool isDataIdLoaded(QString id, QDateTime time);
    Q_INVOKABLE QString loadNewDataImage(QString url);
    Q_INVOKABLE QString get64BasedImage(QString url);
    Q_INVOKABLE void replaceImageFromTmp(QString tmp, QString idImage);
    void loadDataFromId(QString id, QDateTime time);

signals:
    void changed(QString id); // user#id project#id

public slots:
    void onAvatarUserDone(int id, QByteArray data);
    void onAvatarUserFail(int id, QByteArray data);
    void onLogoProjectDone(int id, QByteArray data);
    void onLogoProjectFail(int id, QByteArray data);

private:
    DataImageProvider();

    void callAPI(QString id, QDateTime time);

    QPixmap _AvatarDefault;
    QPixmap _ImageDefault;
    QMap<QString, DataImage*> _Pixmap;
    QMap<int, QString> _LoadingImages;
    QMap<int, QDateTime> _LoadingTimes;
    QList<QPixmap*> _TmpImage;
};

#endif // DATAIMAGEPROVIDER_H
