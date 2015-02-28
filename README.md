# A Application

Comming soon!

# Development Configuration

```xml
<virtualHosts xmlns="http://www.appserver.io/appserver">
    <virtualHost name="at-church.dist">
        <params>
            <param name="documentRoot" type="string">webapps/at-church/dist</param>
        </params>
    </virtualHost>
    <virtualHost name="at-church.dev">
        <params>
            <param name="documentRoot" type="string">webapps/at-church</param>
        </params>
        <rewrites>
            <rewrite condition="-f{OR}.*\.do.*" target="" flag="L" />
            <rewrite condition="^/(.*\.css)$" target="/.tmp/$1" flag="L" />
            <rewrite condition="^/(.*)$" target="/app/$1" flag="L" />
        </rewrites>
    </virtualHost>
</virtualHosts>
```