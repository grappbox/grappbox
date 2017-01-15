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
    Q_INVOKABLE bool isDataIdLoaded(QString id);
    Q_INVOKABLE QString loadNewDataImage(QString url);
    Q_INVOKABLE QString get64BasedImage(QString url);
    Q_INVOKABLE void replaceImageFromTmp(QString tmp, QString idImage);
    Q_INVOKABLE void loadDataFromId(QString id);

signals:
    void changed(QString id, QString url); // user#id project#id

public slots:
    void onAvatarUserDone(int id, QByteArray data);
    void onAvatarUserFail(int id, QByteArray data);
    void onLogoProjectDone(int id, QByteArray data);
    void onLogoProjectFail(int id, QByteArray data);

private:
    DataImageProvider();

    void callAPI(QString id);

    QPixmap _AvatarDefault;
    QPixmap _ImageDefault;
    QMap<QString, DataImage*> _Pixmap;
    QMap<int, QString> _LoadingImages;
    QMap<QString, QString> _LoadedImages;
    QList<QPixmap*> _TmpImage;
};

#endif // DATAIMAGEPROVIDER_H
