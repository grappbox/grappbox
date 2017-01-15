#include "SaveInfoManager.h"
#include <QDebug>

static SaveInfoManager *__SAVEINFO__INSTANCE = nullptr;

SaveInfoManager::SaveInfoManager(QObject *parent) : QObject(parent)
{
    settings = new QSettings("GrappBox", "Save info");
}

SaveInfoManager::~SaveInfoManager()
{

}

SaveInfoManager *SaveInfoManager::instance()
{
    if (!__SAVEINFO__INSTANCE)
        __SAVEINFO__INSTANCE = new SaveInfoManager();
    return __SAVEINFO__INSTANCE;
}

QVariant SaveInfoManager::get(QString key, QVariant defaultValue)
{
    int id = API::SDataManager::GetDataManager()->user()->id();
    qDebug() << id;
    return instance()->settings->value(QVariant(id).toString() + "/" + key, defaultValue);
}

void SaveInfoManager::set(QString key, QVariant value)
{
    int id = API::SDataManager::GetDataManager()->user()->id();
    instance()->settings->setValue(QVariant(id).toString() + "/" + key, value);
}

bool SaveInfoManager::has(QString key)
{
    int id = API::SDataManager::GetDataManager()->user()->id();
    return instance()->settings->contains(QVariant(id).toString() + "/" + key);
}

void SaveInfoManager::setClipboard(QString text)
{
    QClipboard *cliboard = QApplication::clipboard();
    cliboard->setText(text);
}
