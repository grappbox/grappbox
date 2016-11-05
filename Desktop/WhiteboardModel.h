#ifndef WHITEBOARDMODEL_H
#define WHITEBOARDMODEL_H

#include <QObject>
#include <QJSValue>
#include <QJSEngine>
#include <QJSValueIterator>
#include <QJsonObject>
#include <QDateTime>
#include <QTimer>
#include "Manager/SInfoManager.h"
#include "API/SDataManager.h"

class WhiteboardData : public QObject
{
    Q_OBJECT
    Q_PROPERTY(QString title READ title WRITE setTitle NOTIFY titleChanged)
    Q_PROPERTY(QDateTime creationDate READ creationDate WRITE setCreationDate NOTIFY creationDateChanged)
    Q_PROPERTY(QDateTime editDate READ editDate WRITE setEditDate NOTIFY editDateChanged)
    Q_PROPERTY(int id READ id WRITE setId NOTIFY idChanged)
    Q_PROPERTY(QVariantList content READ content WRITE setContent NOTIFY contentChanged)

public:
    WhiteboardData() {}
    WhiteboardData(QJsonObject obj)
    {
        modifyByJsonObject(obj);
    }

    void modifyByJsonObject(QJsonObject obj)
    {
        m_title = obj["name"].toString();
        m_id = obj["id"].toInt();
        m_creationDate = JSON_TO_DATETIME(obj["createdAt"].toString());
        m_editDate = JSON_TO_DATETIME(obj["updatedAt"].toString());
         qDebug() << obj["createdAt"].toString() << " : " << m_creationDate.isValid();
        emit titleChanged(title());
        emit idChanged(id());
        emit creationDateChanged(creationDate());
        emit editDateChanged(editDate());
    }

    void loadContent(QJsonArray obj, bool updateContent = true)
    {
        qDebug() << "Load new content ! (" << obj.size() << ")";
        QDateTime time;
        bool isInitialized = false;
        QList<QVariantMap> values;
        for (QJsonValueRef ref : obj)
        {
            m_ids.push_back(ref.toObject()["id"].toInt());
            values.push_back(jsonToJS(ref.toObject()["object"].toObject()));
            QDateTime tmpTime = JSON_TO_DATETIME(ref.toObject()["createdAt"].toString());
            if (!isInitialized || tmpTime > time)
            {
                isInitialized = true;
                time = tmpTime;
            }
        }
        for (QVariantMap val : values)
        {
            m_content.push_back(val);
        }
        if (updateContent)
        {
            contentChanged(content());
            qDebug() << "Call content Change";
        }
        if (time > m_editDate)
            setEditDate(time);
    }

    void addContent(QJsonObject obj)
    {
        m_ids.push_back(obj["id"].toInt());
        m_content.push_back(jsonToJS(obj["object"].toObject()));
        contentChanged(content());
    }

    void removeContent(int id)
    {
        int idx = m_ids.indexOf(id);
        m_ids.removeAt(idx);
        m_content.removeAt(idx);
        contentChanged(content());
    }

    void removeContents(QJsonArray obj, bool updateContent = true)
    {
        qDebug() << "Remove content !(" << obj.size() << ")";
        QDateTime time;
        bool isInitialized = false;
        for (QJsonValueRef ref : obj)
        {
            int idx = m_ids.indexOf(ref.toObject()["id"].toInt());
            QDateTime tmpTime = JSON_TO_DATETIME(ref.toObject()["deletedAt"].toString());
            if (!isInitialized || tmpTime > time)
            {
                isInitialized = true;
                time = tmpTime;
            }
            m_ids.removeAt(idx);
            m_content.removeAt(idx);
        }
        if (updateContent)
            contentChanged(content());
        if (time > m_editDate)
            setEditDate(time);
    }

    static QVariantMap jsonToJS(QJsonObject obj)
    {
        QVariantMap ret;
        for (QJsonObject::iterator it = obj.begin(); it != obj.end(); ++it)
        {
            if (it.value().isObject())
                ret[it.key()] = jsonToJS(it.value().toObject());
            else if (it.value().isDouble())
                    ret[it.key()] = it.value().toInt();
            else if  (it.value().isString())
                ret[it.key()] = it.value().toString();
            else if (it.value().isBool())
                ret[it.key()] = it.value().toBool();
            else if (it.value().isArray())
            {
                QVariantList list;
                for (QJsonValueRef ref : it.value().toArray())
                {
                    list.push_back(jsonToJS(ref.toObject()));
                }
                ret[it.key()] = list;
            }
        }
        return ret;
    }

    static QJsonObject JSToJson(QVariantMap obj)
    {
        QJsonObject ret;
        for (QVariantMap::iterator it = obj.begin(); it != obj.end(); ++it)
        {
            if (it.value().type() == QVariant::Map)
                ret[it.key()] = JSToJson(it.value().toMap());
            else if (it.value().type() == QVariant::Bool)
                ret[it.key()] = it.value().toBool();
            else if (it.value().type() == QVariant::String)
                ret[it.key()] = it.value().toString();
            else if (it.value().type() == QVariant::Int)
                ret[it.key()] = it.value().toInt();
            else if (it.value().type() == QVariant::Double)
                ret[it.key()] = it.value().toDouble();
            else if (it.value().type() == QVariant::List)
            {
                QJsonArray ar;
                for (QVariant item : it.value().toList())
                {
                    ar.append(JSToJson(item.toMap()));
                }
                ret[it.key()] = ar;
            }
        }
        return ret;
    }

    QString title() const
    {
        return m_title;
    }

    QDateTime creationDate() const
    {
        return m_creationDate;
    }

    QDateTime editDate() const
    {
        return m_editDate;
    }

    int id() const
    {
        return m_id;
    }

    QVariantList content() const
    {
        return m_content;
    }

signals:

    void titleChanged(QString title);

    void creationDateChanged(QDateTime creationDate);

    void editDateChanged(QDateTime editDate);

    void idChanged(int id);

    void contentChanged(QVariantList content);

public slots:

    void setTitle(QString title)
    {
        if (m_title == title)
            return;

        m_title = title;
        emit titleChanged(title);
    }

    void setCreationDate(QDateTime creationDate)
    {
        if (m_creationDate == creationDate)
            return;

        m_creationDate = creationDate;
        emit creationDateChanged(creationDate);
    }

    void setEditDate(QDateTime editDate)
    {
        if (m_editDate == editDate)
            return;

        m_editDate = editDate;
        emit editDateChanged(editDate);
    }

    void setId(int id)
    {
        if (m_id == id)
            return;

        m_id = id;
        emit idChanged(id);
    }

    void setContent(QVariantList content)
    {
        if (m_content == content)
            return;

        m_content = content;
        emit contentChanged(content);
    }

private:
    QString m_title;
    QDateTime m_creationDate;
    QDateTime m_editDate;
    int m_id;
    QList<int> m_ids;
    QVariantList m_content;
};

class WhiteboardModel : public QObject
{
    Q_OBJECT
    Q_PROPERTY(QVariantList whiteboardList READ whiteboardList WRITE setWhiteboardList NOTIFY whiteboardListChanged)
    Q_PROPERTY(int currentItem READ currentItem WRITE setCurrentItem NOTIFY currentItemChanged)

public:
    explicit WhiteboardModel(QObject *parent = 0);

    QVariantList whiteboardList() const
    {
        return m_whiteboardList;
    }

    int currentItem() const
    {
        return m_currentItem;
    }

    Q_INVOKABLE void updateList();
    Q_INVOKABLE void createWhiteboard(QString name);
    Q_INVOKABLE void deleteWhiteboard(int id);
    Q_INVOKABLE void openWhiteboard(int id);
    Q_INVOKABLE void closeWhiteboard();
    Q_INVOKABLE int pushObject(QVariantMap obj);
    Q_INVOKABLE void pullObject();
    Q_INVOKABLE void removeObjectAt(QVariantMap center, float radius);

signals:
    void updatedObject(int id);
    void whiteboardListChanged(QVariantList whiteboardList);
    void currentItemChanged(int currentItem);
    void forceUpdate();

public slots:
    void setWhiteboardList(QVariantList whiteboardList)
    {
        if (m_whiteboardList == whiteboardList)
            return;

        m_whiteboardList = whiteboardList;
        emit whiteboardListChanged(whiteboardList);
    }
    void setCurrentItem(int currentItem)
    {
        if (m_currentItem == currentItem)
            return;

        m_currentItem = currentItem;
        emit currentItemChanged(currentItem);
    }

    void onUpdateListDone(int id, QByteArray data);
    void onUpdateListFail(int id, QByteArray data);
    void onCreateWhiteboardDone(int id, QByteArray data);
    void onCreateWhiteboardFail(int id, QByteArray data);
    void onDeleteWhiteboardDone(int id, QByteArray data);
    void onDeleteWhiteboardFail(int id, QByteArray data);
    void onOpenWhiteboardDone(int id, QByteArray data);
    void onOpenWhiteboardFail(int id, QByteArray data);
    void onCloseWhiteboardDone(int id, QByteArray data);
    void onCloseWhiteboardFail(int id, QByteArray data);
    void onPushObjectDone(int id, QByteArray data);
    void onPushObjectFail(int id, QByteArray data);
    void onPullObjectDone(int id, QByteArray data);
    void onPullObjectFail(int id, QByteArray data);
    void onRemoveObjectDone(int id, QByteArray data);
    void onRemoveObjectFail(int id, QByteArray data);

    void updateWhiteboard();

private:
    QVariantList m_whiteboardList;
    int m_currentItem;
    int m_currentId;
    QTimer *m_timer;
};

#endif // WHITEBOARDMODEL_H
