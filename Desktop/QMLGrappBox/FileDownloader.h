#ifndef FILEDOWNLOADER_H
#define FILEDOWNLOADER_H

#include <QObject>
#include <QByteArray>
#include <QNetworkAccessManager>
#include <QNetworkRequest>
#include <QNetworkReply>

class FileDownloader : public QObject
{
    Q_OBJECT
public:
    explicit FileDownloader(QUrl url, QObject *parent = 0);
    virtual ~FileDownloader();
    QByteArray DownloadedData() const;

signals:
    void Downloaded();
    void Progress(float purcent);

private slots:
    void FileDownloaded(QNetworkReply* reply);
    void FileProgress(qint64 bytesSent, qint64 bytesTotal);

private:
    QNetworkAccessManager _WebCtrl;
    QByteArray _DownloadedData;
};

#endif // FILEDOWNLOADER_H
