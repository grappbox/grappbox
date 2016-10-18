#ifndef SDEBUGLOG_H
#define SDEBUGLOG_H

#include <QDebug>
#include <QFile>

#define LOG(str) SDebugLog::WriteLog(str)

class SDebugLog
{
private:
    SDebugLog();
    ~SDebugLog();

public:
    static void WriteLog(QString data);
    static void DestroyObject();

private:
    QFile *_File;
    QTextStream *_Stream;
};

#endif // SDEBUGLOG_H
