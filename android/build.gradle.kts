buildscript {
    repositories {
        google()
        mavenCentral()
    }
    dependencies {
        classpath("com.android.tools.build:gradle:8.7.3")
    }
}

allprojects {
    repositories {
        google()
        mavenCentral()
    }
}

subprojects {
    val newBuildDir = rootProject.layout.buildDirectory.dir("../../build/${project.name}")
    project.layout.buildDirectory.set(newBuildDir)
}

subprojects {
    val applyCompileOptions: Project.() -> Unit = {
        if (hasProperty("android")) {
            val android = extensions.getByName("android")
            if (android is com.android.build.gradle.BaseExtension) {
                android.compileOptions {
                    sourceCompatibility = JavaVersion.VERSION_17
                    targetCompatibility = JavaVersion.VERSION_17
                }
            }
        }
    }

    if (project.state.executed) {
        project.applyCompileOptions()
    } else {
        project.afterEvaluate {
            project.applyCompileOptions()
        }
    }

    tasks.withType<org.jetbrains.kotlin.gradle.tasks.KotlinCompile>().configureEach {
        kotlinOptions.jvmTarget = "17"
    }
}

tasks.register<Delete>("clean") {
    delete(rootProject.layout.buildDirectory)
}
