#ifndef SAVEINFOMANAGER_H
#define SAVEINFOMANAGER_H

#include <QObject>
#include <QSettings>
#include <QClipboard>
#include <QApplication>
#include "API/SDataManager.h"

class SaveInfoManager : public QObject
{
    Q_OBJECT
private:
    explicit SaveInfoManager(QObject *parent = 0);
    ~SaveInfoManager();

public:
    static SaveInfoManager *instance();
    static QVariant get(QString key, QVariant defaultValue = QVariant(0));
    static void set(QString key, QVariant value);
    static bool has(QString key);
    Q_INVOKABLE void setClipboard(QString text);

signals:

public slots:

private:
    QSettings *settings;
};

#endif // SAVEINFOMANAGER_H
