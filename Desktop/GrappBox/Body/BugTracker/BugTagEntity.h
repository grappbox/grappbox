#ifndef BUGTAGENTITY_H
#define BUGTAGENTITY_H

#include <QString>

class BugTagEntity
{
public:
    explicit        BugTagEntity(int id, QString name);
    bool            operator==(const BugTagEntity &);

public: //Getters
    const int       GetID() const;
    const QString   &GetName() const;

public: //Setters
    void            SetID(const int id);
    void            SetName(const QString &name);

private:
    int             _id;
    QString         _name;
};

#endif // BUGTAGENTITY_H
