#ifndef STATISTICSMODEL_H
#define STATISTICSMODEL_H

#include <QObject>
#include <QVariant>
#include <QVariantList>
#include <QVariantMap>
#include <QJsonObject>
#include <QDebug>
#include "API/SDataManager.h"

class StatisticsModel : public QObject
{
    Q_OBJECT

    Q_PROPERTY(QVariantMap projectInfo READ projectInfo WRITE setProjectInfo NOTIFY projectInfoChanged)
    Q_PROPERTY(QVariantMap bugTrackerInfo READ bugTrackerInfo WRITE setBugTrackerInfo NOTIFY bugTrackerInfoChanged)
    Q_PROPERTY(QVariantMap taskInfo READ taskInfo WRITE setTaskInfo NOTIFY taskInfoChanged)
    Q_PROPERTY(QVariantMap userInfo READ userInfo WRITE setUserInfo NOTIFY userInfoChanged)

    QVariantMap m_projectInfo;

    QVariantMap m_bugTrackerInfo;

    QVariantMap m_taskInfo;

    QVariantMap m_userInfo;

    void UpdateProject(QJsonObject obj);
    void UpdateBugTracker(QJsonObject obj);
    void UpdateTask(QJsonObject obj);
    void UpdateUser(QJsonObject obj);

public:
    StatisticsModel();

    Q_INVOKABLE void updateStatisticsInfo();

    QVariantMap projectInfo() const
    {
        return m_projectInfo;
    }
    QVariantMap bugTrackerInfo() const
    {
        return m_bugTrackerInfo;
    }

    QVariantMap taskInfo() const
    {
        return m_taskInfo;
    }

    QVariantMap userInfo() const
    {
        return m_userInfo;
    }

public slots:
    void setProjectInfo(QVariantMap projectInfo)
    {
        if (m_projectInfo == projectInfo)
            return;

        m_projectInfo = projectInfo;
        emit projectInfoChanged(projectInfo);
    }
    void setBugTrackerInfo(QVariantMap bugTrackerInfo)
    {
        if (m_bugTrackerInfo == bugTrackerInfo)
            return;

        m_bugTrackerInfo = bugTrackerInfo;
        emit bugTrackerInfoChanged(bugTrackerInfo);
    }

    void setTaskInfo(QVariantMap taskInfo)
    {
        if (m_taskInfo == taskInfo)
            return;

        m_taskInfo = taskInfo;
        emit taskInfoChanged(taskInfo);
    }

    void setUserInfo(QVariantMap userInfo)
    {
        if (m_userInfo == userInfo)
            return;

        m_userInfo = userInfo;
        emit userInfoChanged(userInfo);
    }

    void OnUpdateDone(int id, QByteArray array);
    void OnUpdateFail(int id, QByteArray array);

signals:
    void projectInfoChanged(QVariantMap projectInfo);
    void bugTrackerInfoChanged(QVariantMap bugTrackerInfo);
    void taskInfoChanged(QVariantMap taskInfo);
    void userInfoChanged(QVariantMap userInfo);

    void loaded();
};

#endif // STATISTICSMODEL_H
