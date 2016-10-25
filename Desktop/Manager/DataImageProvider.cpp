#include <QStandardPaths>
#include <QDebug>
#include <QDir>
#include <QBuffer>
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
    BEGIN_REQUEST_ADV(this, isProject ? "onLogoProjectDone" : "onAvatarUserDone", isProject ? "onLogoProjectFail" : "onAvatarUserFail");
    {
        ADD_HEADER_FIELD("Authorization", USER_TOKEN);
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
    else if (id.contains("tmp#"))
    {
        int i = QVariant(id.split("#")[1]).toInt();
        ret = *_TmpImage[i];
    }
    else
        ret = id.contains("user") ? _AvatarDefault : _ImageDefault;
    if (size)
        *size = ret.size();
    return ret;
}



bool DataImageProvider::isDataIdLoaded(QString id, QDateTime time)
{
    if (id.contains("tmp#"))
        return true;
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

QString DataImageProvider::loadNewDataImage(QString url)
{
    url = url.mid(8, url.size() - 8);
    _TmpImage.push_back(new QPixmap(url));
    qDebug() << *_TmpImage[0] << " : " << url;
    return "tmp#" + QVariant(_TmpImage.size() - 1).toString();
}

QString DataImageProvider::get64BasedImage(QString url)
{
    QPixmap ret;
    if (url.contains("tmp#"))
    {
        ret = *_TmpImage[QVariant(url.split("#")[1]).toInt()];
    }
    else if (_Pixmap.contains(url))
    {
        ret = _Pixmap[url]->pixmap;
    }
    qDebug() << ret;

    QBuffer encodedImage;
    ret.save(&encodedImage, "PNG");

    return QString(encodedImage.buffer().toBase64().toStdString().c_str());
}

void DataImageProvider::replaceImageFromTmp(QString tmp, QString idImage)
{
    DataImage *image = _Pixmap[idImage];
    image->pixmap = *_TmpImage[QVariant(tmp.split("#")[1]).toInt()];
}

void DataImageProvider::loadDataFromId(QString id, QDateTime time)
{
    if (!id.contains("user#") && !id.contains("project#"))
        return;
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
    qDebug() << "Load data from " << id;
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
                qDebug() << "For id #" << id << " : " << map;
                emit changed(id);
            }
        }
        else
        {
            callAPI(id, time);
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
    //SInfoManager::GetManager()->error("Project", "Unable to retrieve user logo.");
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

    qDebug() << "Pix Project = " << pix;
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
    //SInfoManager::GetManager()->error("Project", "Unable to retrieve project logo.");
}
