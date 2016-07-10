#include "SInfoManager.h"

static SInfoManager *__INSTANCE__SInfoManager = nullptr;

SInfoManager::SInfoManager(QObject *parent) : QObject(parent)
{
}

SInfoManager::~SInfoManager()
{
}

void SInfoManager::emitInfo(QString infoMessage)
{
    emit info(infoMessage);
}

void SInfoManager::emitError(QString infoData)
{
    emit error("An error as encoured", "An error as encoured. Please try again later.");
}

void SInfoManager::emitError(QString title, QString message)
{
    emit error(title, message);
}

SInfoManager *SInfoManager::GetManager()
{
    if (__INSTANCE__SInfoManager == nullptr)
        __INSTANCE__SInfoManager = new SInfoManager();
    return __INSTANCE__SInfoManager;
}
