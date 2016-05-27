#include "API/SDataManager.h"
#include "CloudController.h"
#include <QDebug>
#include <QtMath>
#include <QStandardPaths>

/*
 * File Data
*/

FileData::FileData(QString title, bool protect, bool directory)
{
    _FileName = title;
    _IsDirectory = directory;
    _IsProtected = protect;
}

QString FileData::fileName() const
{
    return _FileName;
}

bool FileData::isDirectory() const
{
    return _IsDirectory;
}

bool FileData::isProtected() const
{
    return _IsProtected;
}

void FileData::setFileName(QString name)
{
    _FileName = name;
    emit fileNameChanged();
}

void FileData::setIsDirectory(bool directory)
{
    _IsDirectory = directory;
    emit isDirectoryChanged();
}

/*
 * File Data Transit
*/

FileDataTransit::FileDataTransit(QUrl name, TransitType type, QString password)
{
    _FileName = name;
    _IsUpload = type;
    _Progress = 0;
    _IsWaiting = true;
}

QString FileDataTransit::fileName()
{
    return _FileName.fileName();
}

double FileDataTransit::progress()
{
    return _Progress;
}

FileDataTransit::TransitType FileDataTransit::uploadType()
{
    return _IsUpload;
}

bool FileDataTransit::isWaiting()
{
    return _IsWaiting;
}

QString FileDataTransit::password()
{
    return _Password;
}

void FileDataTransit::setIsWaiting(bool value)
{
    _IsWaiting = value;
    emit isWaitingChanged();
}

void FileDataTransit::setProgress(double value)
{
    _Progress = value;
    emit progressChanged();
}

void FileDataTransit::setPassword(QString password)
{
    _Password = password;
}

QUrl FileDataTransit::url()
{
    return _FileName;
}

/*
 * Cloud controller
*/

CloudController::CloudController(QObject *parent) : QObject(parent)
{
    _Path = "/";
    _CurrentFile = nullptr;
    _CurrentStream = nullptr;
    _CurrentTransit = nullptr;
    _DataRead = nullptr;

}

void CloudController::loadDirectory()
{
    BEGIN_REQUEST;
    {
        SET_CALL_OBJECT(this);
        SET_ON_DONE("OnLSSuccess");
        SET_ON_FAIL("OnLSFailed");
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(PROJECT);
        ADD_URL_FIELD(QString(_Path).replace("/", ","));
        if (_Path.startsWith("/Safe"))
            ADD_URL_FIELD(_PasswordSafe);
        GET(API::DP_CLOUD, API::GR_LIST_CLOUD);
    }
    END_REQUEST;
    _IsLoading = true;
    emit isLoadingChanged();
}

void CloudController::createDirectory(QString dirName)
{
    BEGIN_REQUEST;
    {
        SET_CALL_OBJECT(this);
        SET_ON_DONE("OnCreateSuccess");
        SET_ON_FAIL("OnCreateFailed");
        ADD_FIELD("token", USER_TOKEN);
        ADD_FIELD("project_id", PROJECT);
        ADD_FIELD("path", _Path);
        ADD_FIELD("dir_name", dirName);
        if (_Path.startsWith("/Safe"))
            ADD_FIELD("password", _PasswordSafe);
        POST(API::DP_CLOUD, API::PR_CREATE_DIRECTORY);
    }
    END_REQUEST;
    _IsLoading = true;
    emit isLoadingChanged();
}

void CloudController::enterDirectory(QString dirName, QString password)
{
    if (_Path == "/")
        _Path += dirName;
    else
        _Path += "/" + dirName;
    if (_Path == "/Safe")
        _PasswordSafe = password;
    loadDirectory();
}

void CloudController::goBack()
{
    int i = _Path.size() - 1;
    while (_Path[i] != '/')
    {
        --i;
    }
    if (i == 0)
        _Path = "/";
    else
        _Path.resize(i - 1);
    loadDirectory();
}

void CloudController::goToDirectoryIndex(int index)
{
    int i = 0;
    int tmpIndex = 0;
    while (i < _Path.size())
    {
        if (_Path[i] == '/')
        {
            tmpIndex++;
            if (tmpIndex == index + 1)
                break;
        }
        ++i;
    }
    if (i == _Path.size())
        return;
    if (i == 0)
        _Path = "/";
    else
        _Path.resize(i);
    loadDirectory();
}

void CloudController::sendFiles(QList<QUrl> files)
{
    bool pending = _PendingFiles.size() > 0;
    for(QUrl url : files)
    {
        FileDataTransit *transit = new FileDataTransit(url);
        _PendingFiles.push_back(transit);
        _FilesUpload.push_back(transit);
    }
    emit uploadingFilesChanged();
    if (pending)
        return;
    _CurrentTransit = *_PendingFiles.begin();
    _CurrentTransit->setIsWaiting(false);
    if (_CurrentFile)
    {
        delete _CurrentFile;
        _CurrentFile = nullptr;
    }
    if (_CurrentStream)
    {
        delete _CurrentStream;
        _CurrentStream = nullptr;
    }
    _CurrentFile = new QFile(_CurrentTransit->url().toLocalFile());
    if (!_CurrentFile->open(QIODevice::ReadOnly))
    {
        qDebug() << "Unable to open file !";
    }
    else
    {
        _CurrentStream = new QDataStream(_CurrentFile);
        float tmpChunk = (float)((float)_CurrentFile->size() / (float)BYTE_UPLOAD);
        if (tmpChunk == 0.0f)
            _TotalChunckNumber = (int)tmpChunk;
        else
            _TotalChunckNumber = (int)tmpChunk + 1;
        BEGIN_REQUEST;
        {
            SET_CALL_OBJECT(this);
            SET_ON_DONE("OnOpenStreamSuccess");
            SET_ON_FAIL("OnOpenStreamFailed");
            ADD_URL_FIELD(USER_TOKEN);
            ADD_URL_FIELD(PROJECT);
            if (_Path.startsWith("/Safe"))
                ADD_URL_FIELD(_PasswordSafe);
            ADD_FIELD("path", _Path);
            ADD_FIELD("filename", _CurrentTransit->url().fileName());
            if (_CurrentTransit->password() != "")
                ADD_FIELD("password", _CurrentTransit->password());
            POST(API::DP_CLOUD, API::PR_OPEN_STREAM);
            GENERATE_JSON_DEBUG;
        }
        END_REQUEST;
    }
}

void CloudController::sendFile(QUrl file, QString password)
{
    bool pending = _PendingFiles.size() > 0;
    FileDataTransit *transit = new FileDataTransit(file);
    transit->setPassword(password);
    _PendingFiles.push_back(transit);
    _FilesUpload.push_back(transit);
    emit uploadingFilesChanged();
    if (pending)
        return;
    _CurrentTransit = *_PendingFiles.begin();
    _CurrentTransit->setIsWaiting(false);
    if (_CurrentFile)
    {
        delete _CurrentFile;
        _CurrentFile = nullptr;
    }
    if (_CurrentStream)
    {
        delete _CurrentStream;
        _CurrentStream = nullptr;
    }
    _CurrentFile = new QFile(_CurrentTransit->url().toLocalFile());
    if (!_CurrentFile->open(QIODevice::ReadOnly))
    {
        qDebug() << "Unable to open file !";
    }
    else
    {
        _CurrentStream = new QDataStream(_CurrentFile);
        float tmpChunk = (float)((float)_CurrentFile->size() / (float)BYTE_UPLOAD);
        if (tmpChunk == 0.0f)
            _TotalChunckNumber = (int)tmpChunk;
        else
            _TotalChunckNumber = (int)tmpChunk + 1;
        BEGIN_REQUEST;
        {
            SET_CALL_OBJECT(this);
            SET_ON_DONE("OnOpenStreamSuccess");
            SET_ON_FAIL("OnOpenStreamFailed");
            ADD_URL_FIELD(USER_TOKEN);
            ADD_URL_FIELD(PROJECT);
            if (_Path.startsWith("/Safe"))
                ADD_URL_FIELD(_PasswordSafe);
            ADD_FIELD("path", _Path);
            ADD_FIELD("filename", _CurrentTransit->url().fileName());
            if (_CurrentTransit->password() != "")
                ADD_FIELD("password", _CurrentTransit->password());
            POST(API::DP_CLOUD, API::PR_OPEN_STREAM);
            GENERATE_JSON_DEBUG;
        }
        END_REQUEST;
    }
}

void CloudController::SendChunckFile()
{
    if (_DataRead)
        delete _DataRead;
    _DataRead = new char[BYTE_UPLOAD];
    int len = _CurrentStream->readRawData(_DataRead, 1048576);
    QByteArray array(_DataRead, len);
    array = array.toBase64();
    BEGIN_REQUEST;
    {
        SET_CALL_OBJECT(this);
        SET_ON_DONE("OnSendChunkSuccess");
        SET_ON_FAIL("OnSendChunkFailed");
        ADD_FIELD("token", USER_TOKEN);
        ADD_FIELD("project_id", PROJECT);
        ADD_FIELD("stream_id", _CurrentUploadID);
        ADD_FIELD("current_chunk", _ChunckNumber);
        ADD_FIELD("file_chunk", array);
        ADD_FIELD("chunk_numbers", _TotalChunckNumber);
        PUT(API::DP_CLOUD, API::PUTR_SEND_CHUNK);
    }
    END_REQUEST;
}

void CloudController::deleteFile(QString file, QString password)
{
    QUrl url = _Path + ((_Path == "/") ? "" : "/") + file;
    BEGIN_REQUEST;
    {
        SET_CALL_OBJECT(this);
        SET_ON_DONE("OnDeleteItemSuccess");
        SET_ON_FAIL("OnDeleteItemFailed");
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(PROJECT);
        ADD_URL_FIELD(url.toString().replace("/", ","));
        if (password != "")
            ADD_URL_FIELD(password);
        if (_Path.startsWith("/Safe"))
            ADD_URL_FIELD(_PasswordSafe);
        DELETE_REQ(API::DP_CLOUD, password != "" ? API::DR_DELETE_SECURE_ITEM : API::DR_DELETE_ITEM);
    }
    END_REQUEST;
}

void CloudController::downloadFile(QString file, QString password)
{
    QUrl url = _Path + ((_Path == "/") ? "" : "/") + file;
    FileDataTransit *downloadTransit = new FileDataTransit(url, FileDataTransit::DOWNLOAD);
    downloadTransit->setIsWaiting(true);
    _FilesDownload.push_back(downloadTransit);
    emit downloadingFilesChanged();
    BEGIN_REQUEST;
    {
        SET_CALL_OBJECT(this);
        SET_ON_DONE("OnGetDownloadSuccess");
        SET_ON_FAIL("OnGetDownloadFailed");
        ADD_URL_FIELD(url.toString().replace("/", ","));
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(PROJECT);
        if (password != "")
            ADD_URL_FIELD(password);
        if (_Path.startsWith("/Safe"))
            ADD_URL_FIELD(_PasswordSafe);
        int id = GET(API::DP_CLOUD, password != "" ? API::GR_DOWNLOAD_SECURE_FILE : API::GR_DOWNLOAD_FILE);
        _DownloadURL[id] = downloadTransit;
    }
    END_REQUEST;
}

QQmlListProperty<FileData> CloudController::directories()
{
    return QQmlListProperty<FileData>(this, _Directories);
}

QQmlListProperty<FileData> CloudController::files()
{
    return QQmlListProperty<FileData>(this, _Files);
}

QQmlListProperty<FileDataTransit> CloudController::uploadingFiles()
{
    return QQmlListProperty<FileDataTransit>(this, _FilesUpload);
}

QQmlListProperty<FileDataTransit> CloudController::downloadingFiles()
{
    return QQmlListProperty<FileDataTransit>(this, _FilesDownload);
}

QString CloudController::path() const
{
    return _Path;
}

bool CloudController::isLoading() const
{
    return _IsLoading;
}

bool CloudController::downloadPending() const
{
    return _DownloadPending;
}

void CloudController::OnLSSuccess(int id, QByteArray array)
{
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(array);
    QJsonObject obj = doc.object()["data"].toObject();
    QJsonObject info = doc.object()["info"].toObject();
    if (info["return_code"].toString() == "3.4.9" || info["return_code"].toString() == "3.9.9")
    {
        _PasswordSafe = "";
        emit directoryFailedLoad();
        return;
    }
    _Directories.clear();
    _Files.clear();
    for (QJsonValueRef ref : obj["array"].toArray())
    {
        QJsonObject file = ref.toObject();
        qDebug() << file["filename"].toString() << " is secured ? " << file["is_secured"].toBool();
        FileData *f = new FileData(file["filename"].toString(), file["is_secured"].toBool(), file["type"].toString() == "dir");
        if (f->isDirectory())
            _Directories.push_back(f);
        else
            _Files.push_back(f);
    }
    _IsLoading = false;
    emit pathChanged();
    emit isLoadingChanged();
    emit directoryLoaded();
}

void CloudController::OnLSFailed(int id, QByteArray array)
{
    _IsLoading = false;
    emit isLoadingChanged();
    emit directoryFailedLoad();
}

void CloudController::OnCreateSuccess(int id, QByteArray array)
{
    loadDirectory();
}

void CloudController::OnCreateFailed(int id, QByteArray array)
{
}

void CloudController::OnSendChunkSuccess(int id, QByteArray array)
{
    if (!_CurrentStream->atEnd())
    {
        double value = (_CurrentFile->size() / BYTE_UPLOAD * 100);
        if (value > 100)
            value = 100;
        _CurrentTransit->setProgress(value);
        _ChunckNumber++;
        SendChunckFile();
    }
    else
    {
        BEGIN_REQUEST;
        {
            SET_CALL_OBJECT(this);
            SET_ON_DONE("OnCloseStreamSuccess");
            SET_ON_FAIL("OnCloseStreamFailed");
            ADD_URL_FIELD(USER_TOKEN);
            ADD_URL_FIELD(PROJECT);
            ADD_URL_FIELD(_CurrentUploadID);
            DELETE_REQ(API::DP_CLOUD, API::DR_CLOSE_STREAM);
            GENERATE_JSON_DEBUG;
        }
        END_REQUEST;
    }
}

void CloudController::OnSendChunkFailed(int id, QByteArray array)
{

}

void CloudController::OnOpenStreamSuccess(int id, QByteArray array)
{
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(array);
    QJsonObject obj = doc.object()["data"].toObject();
    _CurrentUploadID = obj["stream_id"].toVariant().toString();
    _ChunckNumber = 0;
    SendChunckFile();
}

void CloudController::OnOpenStreamFailed(int id, QByteArray array)
{
}

void CloudController::OnCloseStreamSuccess(int id, QByteArray array)
{
    _PendingFiles.removeAll(_CurrentTransit);
    if (_PendingFiles.size() == 0)
    {
        loadDirectory();
        return;
    }
    _CurrentTransit = *_PendingFiles.begin();
    _CurrentTransit->setIsWaiting(false);
    if (_CurrentFile)
        delete _CurrentFile;
    if (_CurrentStream)
        delete _CurrentStream;
    _CurrentFile = new QFile(_CurrentTransit->url().toLocalFile());
    _CurrentStream = new QDataStream(_CurrentFile);
    if (!_CurrentFile->open(QIODevice::ReadOnly))
    {
        qDebug() << "Unable to open file !";
    }
    else
    {
        BEGIN_REQUEST;
        {
            SET_CALL_OBJECT(this);
            SET_ON_DONE("OnOpenStreamSuccess");
            SET_ON_FAIL("OnOpenStreamFailed");
            ADD_URL_FIELD(USER_TOKEN);
            ADD_URL_FIELD(PROJECT);
            ADD_FIELD("path", _Path);
            ADD_FIELD("filename", _CurrentTransit->url().fileName());
            if (_CurrentTransit->password() != "")
                ADD_FIELD("password", _CurrentTransit->password());
            POST(API::DP_CLOUD, API::PR_OPEN_STREAM);
            GENERATE_JSON_DEBUG;
        }
        END_REQUEST;
    }
    loadDirectory();
}

void CloudController::OnCloseStreamFailed(int id, QByteArray array)
{
}

void CloudController::OnGetDownloadSuccess(int id, QByteArray array)
{
    QString urlDownload = QString(array);
    FileDownloader *download = new FileDownloader(urlDownload);
    QObject::connect(download, SIGNAL(Progress(float)), this, SLOT(OnDownloadProgress(float)));
    QObject::connect(download, SIGNAL(Downloaded()), this, SLOT(OnDownloadEnd()));
    _CurrentDownload[download] = _DownloadURL[id];
    _DownloadURL[id]->setIsWaiting(false);
    _DownloadURL.remove(id);
}

void CloudController::OnGetDownloadFailed(int, QByteArray array)
{
}

void CloudController::OnDeleteItemSuccess(int, QByteArray array)
{
    loadDirectory();
}

void CloudController::OnDeleteItemFailed(int, QByteArray array)
{

}

void CloudController::OnDownloadEnd()
{
    QString pathDownload = QStandardPaths::writableLocation(QStandardPaths::DownloadLocation);
    FileDownloader *downloader = static_cast<FileDownloader*>(QObject::sender());
    if (downloader != nullptr && _CurrentDownload.contains(downloader))
    {
        QString pathFile = pathDownload + "/" + _CurrentDownload[downloader]->fileName();
        QFileInfo info(pathFile);
        int i = 1;
        qDebug() << pathFile;
        while (info.exists())
        {
            pathFile = info.dir().path() + "/" + info.baseName() + "(" + QVariant(i).toString() + ")." + info.completeSuffix();
            info = QFileInfo(pathFile);
            qDebug() << pathFile;
            ++i;
        }
        QFile file(pathFile);
        if (!file.open(QIODevice::WriteOnly | QIODevice::Truncate))
        {
            qDebug() << "Unable to open file " << pathFile;
        }
        else
        {
            QDataStream stream(&file);
            QByteArray array = downloader->DownloadedData();
            stream.writeRawData(array.data(), array.size());
            file.close();
        }
    }
}

void CloudController::OnDownloadFail()
{

}

void CloudController::OnDownloadProgress(float value)
{
    FileDownloader *downloader = static_cast<FileDownloader*>(QObject::sender());
    if (downloader != nullptr && _CurrentDownload.contains(downloader))
    {
        qDebug() << "Set progress to " << value;
        _CurrentDownload[downloader]->setProgress(value);
    }
}
