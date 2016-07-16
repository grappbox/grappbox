#ifndef SINFOMANAGER_H
#define SINFOMANAGER_H

#include <QObject>
#include <QMap>

class SInfoManager : public QObject
{
    Q_OBJECT
public:
    void emitInfo(QString infoMessage);
    void emitError(QString infoData);
    void emitError(QString title, QString message);

    static SInfoManager *GetManager();

signals:
    void error(QString title, QString message);
    void info(QString message);

public slots:


private:
    SInfoManager(QObject *parent = 0);
    ~SInfoManager();
};

#endif // SINFOMANAGER_H
