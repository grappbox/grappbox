#include <QStandardPaths>
#include <QDebug>
#include <QDir>
#include "DataImageProvider.h"

static DataImageProvider *__INSTANCE__DataImageProvider = nullptr;

DataImageProvider *DataImageProvider::getInstance()
{
    if (__INSTANCE__DataImageProvider == nullptr)
        __INSTANCE__DataImageProvider = new DataImageProvider();
    return __INSTANCE__DataImageProvider;
}

DataImageProvider::DataImageProvider() : QQuickImageProvider(QQuickImageProvider::Pixmap)
{
    _AvatarDefault = QPixmap(":/icons/icons/default-avatar.min.png");
    _ImageDefault = QPixmap(":/icons/icons/default-logo.min.png");
    _Pixmap["project#default"] = new DataImage(_ImageDefault);
    _Pixmap["user#default"] = new DataImage(_AvatarDefault);
}

void DataImageProvider::callAPI(QString id, QDateTime time)
{
    qDebug() << "2";
    bool isProject = id.contains("project#");
    DataImage *dataImg;
    if (_Pixmap.contains(id))
        dataImg = _Pixmap[id];
    else
        dataImg = new DataImage();
    dataImg->isDeprectated = false;
    dataImg->isLoaded = false;
    dataImg->isWaiting = true;
    dataImg->time = time;
    _Pixmap[id] = dataImg;
    qDebug() << id;
    BEGIN_REQUEST_ADV(this, isProject ? "onLogoProjectDone" : "onAvatarUserDone", isProject ? "onLogoProjectFail" : "onAvatarUserFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(id.split("#")[1]);
        int idReq;
        if (isProject)
            idReq = GET(API::DP_PROJECT, API::GR_PROJECT_LOGO);
        else
            idReq = GET(API::DP_USER_DATA, API::GR_USER_AVATAR);
        _LoadingImages[idReq] = id;
        _LoadingTimes[idReq] = time;
    }
    END_REQUEST;
}

QPixmap DataImageProvider::requestPixmap(const QString &id, QSize *size, const QSize &requestedSize)
{
    Q_UNUSED(requestedSize)
    QPixmap ret;
    if (_Pixmap.contains(id))
        ret = _Pixmap[id]->pixmap;
    else
        ret = id.contains("user") ? _AvatarDefault : _ImageDefault;
    if (size)
        *size = ret.size();
    return ret;
}



bool DataImageProvider::isDataIdLoaded(QString id, QDateTime time)
{
    if (_Pixmap.contains(id))
    {
        DataImage *item = _Pixmap[id];
        if (item->isLoaded && item->time == time)
            return true;
        else if (!item->isWaiting)
        {
            if (item->time != time)
            {
                item->time = time;
                item->isLoaded = false;
                item->isDeprectated = true;
            }
            loadDataFromId(id, time);
        }
        return false;
    }
    loadDataFromId(id, time);
    return false;
}

void DataImageProvider::loadDataFromId(QString id, QDateTime time)
{
    QString path = QStandardPaths::writableLocation(QStandardPaths::AppLocalDataLocation);
    if (!QDir(path).exists())
        QDir().mkdir(path);
    QString pathProject = path + "/projects";
    if (!QDir(pathProject).exists())
        QDir().mkdir(pathProject);
    QString pathUsers = path + "/users";
    if (!QDir(pathUsers).exists())
        QDir().mkdir(pathUsers);
    QString realPath = id.contains("user") ? pathUsers : pathProject;
    qDebug() << "1";
    if (!QDir(realPath + "/" + id).exists())
        callAPI(id, time);
    else
    {
        if (_Pixmap.contains(id))
        {
            DataImage *data = _Pixmap[id];
            if (data->isDeprectated)
                callAPI(id, time);
            else if (data->isLoaded)
                emit changed(id);
            else
            {
                QPixmap map(realPath + "/" + id + "/image.png");
                data->isLoaded = true;
                data->isWaiting = false;
                data->pixmap = map;
                emit changed(id);
            }
        }
    }
}

void DataImageProvider::onAvatarUserDone(int id, QByteArray data)
{
    QString realId = _LoadingImages[id];
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QByteArray dataimg = QByteArray::fromBase64(obj["avatar"].toString().toStdString().c_str());
    QImage img = QImage::fromData(dataimg);
    QPixmap pix = QPixmap::fromImage(img);

    qDebug() << "Pix = " << pix;

    bool hasToSave = true;
    if (pix.isNull())
    {
        pix = _AvatarDefault;
        hasToSave = false;
    }

    qDebug() << "pix = " << pix;
    qDebug() << "pixmap contained = " << _Pixmap.contains(realId);

    DataImage *dataImg = _Pixmap[realId];
    dataImg->pixmap = pix;
    dataImg->isDeprectated = false;
    dataImg->isLoaded = true;
    dataImg->isWaiting = false;
    dataImg->time = _LoadingTimes[id];

    _LoadingTimes.remove(id);
    _LoadingImages.remove(id);
    emit changed(realId);

    if (hasToSave)
    {
        QString path = QStandardPaths::writableLocation(QStandardPaths::AppLocalDataLocation);
        if (!QDir(path).exists())
            QDir().mkdir(path);
        QString pathUsers = path + "/users";
        if (!QDir(pathUsers).exists())
            QDir().mkdir(pathUsers);
        if (!QDir(pathUsers + "/" + realId).exists())
            QDir().mkdir(pathUsers + "/" + realId);
        pix.save(pathUsers + "/" + realId + "/image.png", "PNG");
    }
}

void DataImageProvider::onAvatarUserFail(int id, QByteArray data)
{
    Q_UNUSED(id)
    Q_UNUSED(data)
    SInfoManager::GetManager()->error("Project", "Unable to retrieve user logo.");
}

void DataImageProvider::onLogoProjectDone(int id, QByteArray data)
{
    QString realId = _LoadingImages[id];
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QByteArray dataimg = QByteArray::fromBase64(obj["logo"].toString().toStdString().c_str());
    QImage img = QImage::fromData(dataimg);
    QPixmap pix = QPixmap::fromImage(img);

    bool hasToSave = true;
    if (pix.isNull())
    {
        pix = _ImageDefault;
        hasToSave = false;
    }

    DataImage *dataImg = _Pixmap[realId];
    dataImg->pixmap = pix;
    dataImg->isDeprectated = false;
    dataImg->isLoaded = true;
    dataImg->isWaiting = false;
    dataImg->time = _LoadingTimes[id];

    _LoadingTimes.remove(id);
    _LoadingImages.remove(id);
    emit changed(realId);

    if (hasToSave)
    {
        QString path = QStandardPaths::writableLocation(QStandardPaths::AppLocalDataLocation);
        if (!QDir(path).exists())
            QDir().mkdir(path);
        QString pathProject = path + "/projects";
        if (!QDir(pathProject).exists())
            QDir().mkdir(pathProject);
        if (!QDir(pathProject + "/" + realId).exists())
            QDir().mkdir(pathProject + "/" + realId);
        pix.save(pathProject + "/" + realId + "/image.png", "PNG");
    }
}

void DataImageProvider::onLogoProjectFail(int id, QByteArray data)
{
    Q_UNUSED(id)
    Q_UNUSED(data)
    SInfoManager::GetManager()->error("Project", "Unable to retrieve project logo.");
}
