#include "FileDownloader.h"

FileDownloader::FileDownloader(QUrl url, QObject *parent) :
    QObject(parent)
{
    connect(&_WebCtrl, SIGNAL (finished(QNetworkReply*)), this, SLOT (FileDownloaded(QNetworkReply*)));

    QNetworkRequest request(url);
    QNetworkReply *reply = _WebCtrl.get(request);
    connect(reply, SIGNAL(uploadProgress(qint64,qint64)), this, SLOT(FileProgress(qint64,qint64)));
}

FileDownloader::~FileDownloader() { }

void FileDownloader::FileDownloaded(QNetworkReply* reply) {
    _DownloadedData = reply->readAll();
    reply->deleteLater();
    emit Downloaded();
}

QByteArray FileDownloader::DownloadedData() const {
    return _DownloadedData;
}

void FileDownloader::FileProgress(qint64 bytesSent, qint64 bytesTotal)
{
    emit Progress((float)(bytesSent) / (float)(bytesTotal) * 100);
}
