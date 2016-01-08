#ifndef BUGENTITY_H
#define BUGENTITY_H

#include "BugTagEntity.h"
#include <QString>
#include <QList>
#include <QDateTime>
#include <QJsonObject>
#include <QJsonArray>
#include <QMessageBox>

typedef struct SBugState
{
    SBugState();
    ~SBugState();
    explicit SBugState(const int id, const QString &name);

    int _id;
    QString _name;
} SBugState;

class BugEntity {

public: // System
    BugEntity();
    explicit BugEntity(QJsonObject obj);

public: // Getters
    const int                   GetID() const;
    const int                   GetAuthorId() const;
    const int                   GetUserId() const;
    const int                   GetProjectId() const;
    const QString               &GetTitle() const;
    const QString               &GetDescription() const;
    const int                   GetParentId() const;
    const QDateTime             &GetCreatedAt() const;
    const QDateTime             &GetEditedAt() const;
    const QDateTime             &GetDeletedAt() const;
    const SBugState             &GetState() const;
    const QList<BugTagEntity>   &GetTags() const;
    const bool                  IsValid() const;

public: // Setters
    void                        SetAuthorID(const int id);
    void                        SetUserID(const int id);
    void                        SetProjectID(const int id);
    void                        SetTitle(const QString &title);
    void                        SetDescription(const QString &desc);
    void                        SetParentID(const int id);
    void                        SetState(const SBugState &state);
    void                        SetTags(const QList<BugTagEntity> &tags);
    void                        AddTag(const BugTagEntity &tag);
    void                        DelTag(QList<BugTagEntity>::const_iterator tagIt);

private:
    int                         _id;
    int                         _authorId;
    int                         _userId;
    int                         _projectId;
    QString                     _title;
    QString                     _description;
    int                         _parentId;
    QDateTime                   _createdAt;
    QDateTime                   _editedAt;
    QDateTime                   _deletedAt;
    SBugState                   _state;
    QList<BugTagEntity>         _tags;
};

#endif // BUGENTITY_H
