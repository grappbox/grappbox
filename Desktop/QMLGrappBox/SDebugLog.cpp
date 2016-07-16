#include "SDebugLog.h"
#include <QStandardPaths>

static SDebugLog *_InstanceDebug = nullptr;

SDebugLog::SDebugLog()
{
    qDebug() << QStandardPaths::locate(QStandardPaths::TempLocation, "GrappBox.log");
    _File = new QFile("GrappBox.log");
    if (!_File->open(QIODevice::Text | QIODevice::WriteOnly | QIODevice::Truncate))
    {
        qDebug() << "Unable to open log file !";
    }
    else
    {
        _Stream = new QTextStream(_File);
    }
}

SDebugLog::~SDebugLog()
{
    qDebug() << "Close log file";
    _File->close();
    delete _File;
    delete _Stream;
}

void SDebugLog::DestroyObject()
{
    if (_InstanceDebug)
        delete _InstanceDebug;
}

void SDebugLog::WriteLog(QString data)
{
    if (!_InstanceDebug)
    {
        _InstanceDebug = new SDebugLog();
    }
    if (_InstanceDebug->_Stream)
    (*_InstanceDebug->_Stream) << data;
}
