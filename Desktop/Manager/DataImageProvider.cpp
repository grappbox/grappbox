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

void DataImageProvider::callAPI(QString id)
{
    qDebug() << "CALL API";
    bool isProject = id.contains("project#");
    DataImage *dataImg;
    if (_Pixmap.contains(id))
        dataImg = _Pixmap[id];
    else
        dataImg = new DataImage();
    dataImg->isDeprectated = false;
    dataImg->isLoaded = false;
    dataImg->isWaiting = true;
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



bool DataImageProvider::isDataIdLoaded(QString id)
{
    if (id.contains("tmp#"))
        return true;
    loadDataFromId(id);
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
    if (image == nullptr)
    {
        image = new DataImage(*_TmpImage[QVariant(tmp.split("#")[1]).toInt()]);
        image->isLoaded = true;
        _Pixmap[idImage] = image;
    }
    else
        image->pixmap = *_TmpImage[QVariant(tmp.split("#")[1]).toInt()];
}

void DataImageProvider::loadDataFromId(QString id)
{
    if (!id.contains("user#") && !id.contains("project#"))
        return;
    if (_LoadedImages.contains(id))
        emit changed(id, _LoadedImages[id]);
    else
        callAPI(id);
}

void DataImageProvider::onAvatarUserDone(int id, QByteArray data)
{
    QString realId = _LoadingImages[id];
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QByteArray dataimg = QByteArray::fromBase64(obj["avatar"].toString().toStdString().c_str());
    SHOW_JSON(data);

    _LoadingImages.remove(id);

    if (obj["avatar"].isNull())
        emit changed(realId, "image://api/user#default");
    else
    {
        _LoadedImages[realId] = obj["avatar"].toString();
        emit changed(realId, obj["avatar"].toString());
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

    qDebug() << obj["logo"].toString();

    _LoadingImages.remove(id);
    if (obj["logo"].isNull())
        emit changed(realId, "image://api/project#default");
    else
    {
        emit changed(realId, obj["logo"].toString());
        _LoadedImages[realId] = obj["logo"].toString();
    }
}

void DataImageProvider::onLogoProjectFail(int id, QByteArray data)
{
    Q_UNUSED(id)
    Q_UNUSED(data)
    //SInfoManager::GetManager()->error("Project", "Unable to retrieve project logo.");
}
