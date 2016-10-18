#ifndef CLOUDCONTROLLER_H
#define CLOUDCONTROLLER_H

#include <QObject>
#include <QQmlListProperty>
#include <QList>
#include <QUrl>
#include <QFileInfo>
#include <QFile>
#include <QDir>
#include <QDataStream>
#include <QByteArray>

#include "FileDownloader.h"

#define BYTE_UPLOAD 1048576

class FileData : public QObject
{
    Q_OBJECT
    Q_PROPERTY(QString fileName READ fileName WRITE setFileName NOTIFY fileNameChanged)
    Q_PROPERTY(bool isDirectory READ isDirectory WRITE setIsDirectory NOTIFY isDirectoryChanged)
    Q_PROPERTY(bool isProtected READ isProtected NOTIFY isProtectedChanged)

signals:
    void fileNameChanged();
    void isDirectoryChanged();
    void isProtectedChanged();

public:
    FileData(QString title = "", bool protect = false, bool directory = false);
    ~FileData();

    QString fileName() const;
    bool isDirectory() const;
    bool isProtected() const;

    void setFileName(QString name);
    void setIsDirectory(bool directory);

private:
    QString _FileName;
    bool _IsDirectory;
    bool _IsProtected;
};

class FileDataTransit : public QObject
{
    Q_OBJECT
public:
    enum TransitType
    {
        UPLOAD = 0,
        DOWNLOAD
    };

    Q_PROPERTY(QString fileName READ fileName NOTIFY fileNameChanged)
    Q_PROPERTY(double progress READ progress NOTIFY progressChanged)
    Q_PROPERTY(TransitType uploadType READ uploadType NOTIFY uploadTypeChanged)
    Q_PROPERTY(bool isWaiting READ isWaiting NOTIFY isWaitingChanged)

signals:
    void fileNameChanged();
    void progressChanged();
    void uploadTypeChanged();
    void isWaitingChanged();

public:
    FileDataTransit(QUrl name = QUrl(), TransitType type = UPLOAD, QString password = "");

    QString fileName();
    double progress();
    TransitType uploadType();
    bool isWaiting();
    Q_INVOKABLE QUrl url();
    QString password();

    void setIsWaiting(bool value);
    void setProgress(double value);
    void setPassword(QString password);

    Q_INVOKABLE void setUrl(QString url)
    {
        _FileName = url;
    }

private:
    QUrl _FileName;
    double _Progress;
    TransitType _IsUpload;
    bool _IsWaiting;
    QString _Password;
};

class CloudController : public QObject
{
    Q_OBJECT
    Q_PROPERTY(QQmlListProperty<FileData> files READ files NOTIFY directoryLoaded)
    Q_PROPERTY(QQmlListProperty<FileData> directories READ directories NOTIFY directoryLoaded)
    Q_PROPERTY(QQmlListProperty<FileDataTransit> uploadingFiles READ uploadingFiles NOTIFY uploadingFilesChanged)
    Q_PROPERTY(QQmlListProperty<FileDataTransit> downloadingFiles READ downloadingFiles NOTIFY downloadingFilesChanged)
    Q_PROPERTY(QString path READ path NOTIFY pathChanged)
    Q_PROPERTY(bool isLoading READ isLoading NOTIFY isLoadingChanged)
    Q_PROPERTY(bool downloadPending READ downloadPending NOTIFY downloadPendingChanged)

public:
    explicit CloudController(QObject *parent = 0);
    ~CloudController();

    Q_INVOKABLE void loadDirectory();
    Q_INVOKABLE void createDirectory(QString dirName);
    Q_INVOKABLE void enterDirectory(QString dirName, QString password = "");
    Q_INVOKABLE void goBack();
    Q_INVOKABLE void goToDirectoryIndex(int index);
    Q_INVOKABLE void sendFiles(QList<QUrl> files);
    Q_INVOKABLE void sendFile(QUrl file, QString password);
    Q_INVOKABLE void deleteFile(QString file, QString password = "");
    Q_INVOKABLE void downloadFile(QString file, QString password = "");
    Q_INVOKABLE void openFile(QString file);

    QQmlListProperty<FileData> directories();
    QQmlListProperty<FileData> files();
    QQmlListProperty<FileDataTransit> uploadingFiles();
    QQmlListProperty<FileDataTransit> downloadingFiles();
    QString path() const;
    bool isLoading() const;
    bool downloadPending() const;

signals:
    void directoryLoaded();
    void directoryFailedLoad();
    void pathChanged();
    void isLoadingChanged();

    void uploadingFilesChanged();
    void downloadingFilesChanged();
    void downloadPendingChanged();

public slots:
    void OnLSSuccess(int id, QByteArray array);
    void OnLSFailed(int id, QByteArray array);
    void OnCreateSuccess(int id, QByteArray array);
    void OnCreateFailed(int id, QByteArray array);
    void OnSendChunkSuccess(int id, QByteArray array);
    void OnSendChunkFailed(int id, QByteArray array);
    void OnOpenStreamSuccess(int id, QByteArray array);
    void OnOpenStreamFailed(int id, QByteArray array);
    void OnCloseStreamSuccess(int id, QByteArray array);
    void OnCloseStreamFailed(int id, QByteArray array);
    void OnGetDownloadSuccess(int, QByteArray array);
    void OnGetDownloadFailed(int, QByteArray array);
    void OnDeleteItemSuccess(int, QByteArray array);
    void OnDeleteItemFailed(int, QByteArray array);

    void OnDownloadEnd();
    void OnDownloadFail();
    void OnDownloadProgress(float value);

private:
    void SendChunckFile();

    // View information field
    QList<FileData*> _Files;
    QList<FileData*> _Directories;
    QList<FileDataTransit*> _FilesUpload;
    QList<FileDataTransit*> _FilesDownload;
    QList<FileDataTransit*> _PendingFiles;
    QMap<QString, QString> _PasswordSaving;
    QString _Path;
    QString _OldPath;
    QString _PasswordSafe;
    bool _IsLoading;
    bool _DownloadPending;

    // Upload field
    QFile *_CurrentFile;
    QDataStream *_CurrentStream;
    FileDataTransit *_CurrentTransit;
    QString _CurrentUploadID;
    int _ChunckNumber;
    int _TotalChunckNumber;
    char *_DataRead;

    // Download field
    QMap<FileDownloader*, FileDataTransit*> _CurrentDownload;
    QMap<int, FileDataTransit*> _DownloadURL;
};

#endif // CLOUDCONTROLLER_H
